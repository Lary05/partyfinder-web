<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700">
                <div class="p-6 text-gray-100">
                    <h2 class="text-2xl font-bold mb-6 text-white">✏️ Edit event</h2>

                    <form action="{{ route('events.update', $event->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Event name</label>
                            <input type="text" name="title" value="{{ old('title', $event->title) }}" class="w-full bg-gray-900 border-gray-600 rounded-lg text-white focus:ring-blue-500" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Genre</label>
                                <select name="genre" class="w-full bg-gray-900 border-gray-600 rounded-lg text-white">
                                    <option value="Egyéb" {{ $event->genre == 'Egyéb' ? 'selected' : '' }}>Other</option>
                                    <option value="R&B" {{ $event->genre == 'R&B' ? 'selected' : '' }}>R&B</option>
                                    <option value="Hip-Hop" {{ $event->genre == 'Hip-Hop' ? 'selected' : '' }}>Hip-Hop</option>
                                    <option value="Techno" {{ $event->genre == 'Techno' ? 'selected' : '' }}>Techno</option>
                                    <option value="House" {{ $event->genre == 'House' ? 'selected' : '' }}>House</option>
                                    <option value="Retro" {{ $event->genre == 'Retro' ? 'selected' : '' }}>Retro</option>
                                    <option value="Rock" {{ $event->genre == 'Rock' ? 'selected' : '' }}>Rock</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Age limit</label>
                                <select name="age_limit" class="w-full bg-gray-900 border-gray-600 rounded-lg text-white">
                                    <option value="0" {{ $event->age_limit == 0 ? 'selected' : '' }}>None (Unlimited)</option>
                                    <option value="16" {{ $event->age_limit == 16 ? 'selected' : '' }}>16+</option>
                                    <option value="18" {{ $event->age_limit == 18 ? 'selected' : '' }}>18+</option>
                                    <option value="21" {{ $event->age_limit == 21 ? 'selected' : '' }}>21+</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Facebook Event Link</label>
                                <input type="url" name="facebook_url" value="{{ old('facebook_url', $event->facebook_url) }}" placeholder="https://facebook.com/events/..." class="w-full bg-gray-900 border-gray-600 rounded-lg text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Ticket Purchase Link</label>
                                <input type="url" name="ticket_url" value="{{ old('ticket_url', $event->ticket_url) }}" placeholder="https://cooltix.hu/..." class="w-full bg-gray-900 border-gray-600 rounded-lg text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Date</label>
                            <input type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time) }}" class="w-full bg-gray-900 border-gray-600 rounded-lg text-white calendar-dark" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Location</label>
                            <select name="location_id" class="w-full bg-gray-900 border-gray-600 rounded-lg text-white" required>
                                @foreach($cities as $city)
                                    <optgroup label="{{ $city->name }}">
                                        @foreach($city->locations as $location)
                                            <option value="{{ $location->id }}" {{ $event->location_id == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }} ({{ $location->address }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
                            <textarea name="description" rows="4" class="w-full bg-gray-900 border-gray-600 rounded-lg text-white">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Borítókép módosítása (Opcionális)</label>
                            <div class="flex items-center bg-gray-900 border border-gray-600 rounded-lg p-2">
                                <input type="file" id="edit_image_upload" name="image" accept="image/*" class="hidden" 
                                       onchange="document.getElementById('edit-image-file-name').textContent = this.files[0] ? this.files[0].name : 'No file chosen'">
                                <label for="edit_image_upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 rounded-full text-white font-semibold text-sm hover:bg-blue-700 transition">
                                    Choose File
                                </label>
                                <span id="edit-image-file-name" class="ml-4 text-sm text-gray-400">No file chosen</span>
                            </div>
                        </div>

                        <div class="flex justify-between pt-4">
                            <button type="button" onclick="if(confirm('Biztosan törlöd ezt az eseményt? Nem vonható vissza!')) document.getElementById('delete-form').submit();" class="text-red-500 hover:text-red-400 font-bold px-4 py-2">
                                🗑️ Event delete
                            </button>

                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition">
                                💾 Save changes
                            </button>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('events.destroy', $event->id) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>