<x-app-layout>
    <div class="py-12 bg-gray-900 min-h-screen text-white">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 flex justify-between items-center">
                <h2 class="text-3xl font-extrabold text-white">
                    <span class="text-blue-500">Admin</span> Dashboard
                </h2>
                <p class="text-gray-400">Pending events for approval</p>
            </div>

            @if (session('status'))
                <div class="mb-6 bg-green-900/30 border border-green-500 text-green-400 p-4 rounded-lg font-bold">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-700">
                <div class="p-6">
                    @if($pendingEvents->isEmpty())
                        <div class="text-center py-10 text-gray-500">
                            <i class="fas fa-check-circle text-5xl mb-4 opacity-20"></i>
                            <p class="text-xl">No pending events. Everything is clean! ✨</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-700">
                                        <th class="py-4 px-4 text-gray-400 uppercase text-xs">Event</th>
                                        <th class="py-4 px-4 text-gray-400 uppercase text-xs">Location</th>
                                        <th class="py-4 px-4 text-gray-400 uppercase text-xs">Date</th>
                                        <th class="py-4 px-4 text-gray-400 uppercase text-xs text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingEvents as $event)
                                        <tr class="border-b border-gray-700 hover:bg-gray-750 transition">
                                            <td class="py-4 px-4 font-bold">{{ $event->title }}</td>
                                            <td class="py-4 px-4 text-gray-400">{{ $event->location->name ?? 'Unknown' }}</td>
                                            <td class="py-4 px-4 text-sm text-blue-400">{{ $event->start_time }}</td>
                                            <td class="py-4 px-4 text-right">
                                                <form action="{{ route('admin.events.approve', $event->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-lg shadow-green-900/20">
                                                        Approve & Publish
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>