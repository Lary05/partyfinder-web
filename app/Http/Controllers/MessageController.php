<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Public API Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/conversations
     *
     * Return all active conversations the authenticated user is a part of.
     * Includes the other participant's profile, latest message, and unread count.
     */
    public function getConversationsList(Request $request)
    {
        $authId = auth()->id();

        // 1. Get all conversation IDs for the auth user
        $authConvoIds = DB::table('conversation_participants')
            ->where('user_id', $authId)
            ->pluck('conversation_id');

        if ($authConvoIds->isEmpty()) {
            return response()->json([]);
        }

        // 2. Fetch the other participants for these conversations
        $otherParticipants = DB::table('conversation_participants')
            ->whereIn('conversation_id', $authConvoIds)
            ->where('user_id', '!=', $authId)
            ->get()
            ->keyBy('conversation_id');

        // Fetch User models for these other participants
        $userIds = $otherParticipants->pluck('user_id')->unique();
        $users = \App\Models\User::with('photos')->whereIn('id', $userIds)->get()->keyBy('id');

        $conversations = [];

        foreach ($authConvoIds as $convoId) {
            $otherParticipant = $otherParticipants->get($convoId);
            if (!$otherParticipant) continue;

            $otherUser = $users->get($otherParticipant->user_id);
            if (!$otherUser) continue;

            $latestMessage = DB::table('messages')
                ->where('conversation_id', $convoId)
                ->orderBy('created_at', 'desc')
                ->first();

            // Skip empty conversations with no messages
            if (!$latestMessage) continue;

            $unreadCount = DB::table('messages')
                ->where('conversation_id', $convoId)
                ->where('sender_id', '!=', $authId)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'conversation_id' => $convoId,
                'other_user' => $otherUser,
                'latest_message' => $latestMessage,
                'unread_count' => $unreadCount,
            ];
        }

        // Order by latest message descending
        usort($conversations, function ($a, $b) {
            return strtotime($b['latest_message']->created_at) < strtotime($a['latest_message']->created_at) ? 1 : -1;
        });

        return response()->json($conversations);
    }

    /**
     * GET /api/messages/{user}
     *
     * Return all messages in the 1-on-1 conversation between the
     * authenticated user and $userId, ordered oldest-first.
     * Returns an empty array if no conversation exists yet.
     */
    public function getConversation(int $userId)
    {
        $conversationId = $this->findConversationId(auth()->id(), $userId);

        if (!$conversationId) {
            return response()->json([]);
        }

        $messages = DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * POST /api/messages/{user}
     *
     * Send a direct message from the authenticated user to $userId.
     * Creates the conversation and adds both participants if one doesn't exist yet.
     * Returns the newly created message row.
     */
    public function sendMessage(Request $request, int $userId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $authId = auth()->id();

        // We wrap everything in a transaction to prevent partial data (phantom conversations)
        $message = DB::transaction(function () use ($authId, $userId, $request) {
            // Find or create the 1-on-1 conversation
            $conversationId = $this->findConversationId($authId, $userId);

            if (!$conversationId) {
                $conversationId = $this->createConversation($authId, $userId);
            }

            // Insert the new message
            $messageId = DB::table('messages')->insertGetId([
                'conversation_id' => $conversationId,
                'sender_id'       => $authId,
                'content'         => $request->message,
                'is_read'         => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Return the full message row so the mobile app gets all fields
            return DB::table('messages')->find($messageId);
        });

        return response()->json($message, 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Find the conversation_id for an existing 1-on-1 (non-group) conversation
     * that contains exactly both $authId and $userId as participants.
     *
     * Strategy: find conversation IDs that have $authId as a participant,
     * then intersect with those that have $userId as a participant,
     * filtering to is_group = false.
     *
     * @return int|null  The conversation ID, or null if none exists.
     */
    private function findConversationId(int $authId, int $userId): ?int
    {
        // Conversations the auth user belongs to (1-on-1 only)
        $authConvoIds = DB::table('conversation_participants')
            ->where('user_id', $authId)
            ->pluck('conversation_id');

        // From those, find one that also has $userId AND is not a group chat
        $conversationId = DB::table('conversation_participants')
            ->whereIn('conversation_id', $authConvoIds)
            ->where('user_id', $userId)
            ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
            ->where('conversations.is_group', false)
            ->value('conversation_participants.conversation_id');

        return $conversationId ? (int) $conversationId : null;
    }

    /**
     * Create a new 1-on-1 conversation and add both users as participants.
     *
     * @return int  The newly created conversation_id.
     */
    private function createConversation(int $authId, int $userId): int
    {
        // Insert the conversation row
        $conversationId = DB::table('conversations')->insertGetId([
            'name'       => null,   // 1-on-1 DMs don't need a name
            'is_group'   => false,
            'event_id'   => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add both participants
        DB::table('conversation_participants')->insert([
            [
                'conversation_id' => $conversationId,
                'user_id'         => $authId,
            ],
            [
                'conversation_id' => $conversationId,
                'user_id'         => $userId,
            ],
        ]);

        return $conversationId;
    }
}
