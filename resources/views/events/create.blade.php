<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-700">
                
                <div class="p-8 border-b border-gray-700 bg-gray-900">
                    <h2 class="text-2xl font-bold text-white">✨ Creating a New Party</h2>
                    <p class="text-gray-400 text-sm mt-1">Fill in the details and publish the event!</p>
                </div>

                <div class="p-8">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-900/50 border border-red-500 text-red-200 p-4 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Title</label>
                            <textarea name="title" rows="4" placeholder="No title"
                                      class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Borítókép</label>
                            <div class="flex items-center bg-gray-900 border border-gray-600 rounded-lg p-2">
                                <input type="file" id="image_upload" name="image" accept="image/*" class="hidden" 
                                       onchange="document.getElementById('image-file-name').textContent = this.files[0] ? this.files[0].name : 'No file chosen'">
                                <label for="image_upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 rounded-full text-white font-semibold text-sm hover:bg-blue-700 transition">
                                    Choose File
                                </label>
                                <span id="image-file-name" class="ml-4 text-sm text-gray-400">No file chosen</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Location</label>
                                <select name="location_id" required class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Choose a location...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">
                                            {{ $location->name }} ({{ $location->city->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Start Date</label>
                                <input type="datetime-local" name="start_time" required
                                       class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500 [color-scheme:dark]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Description</label>
                            <textarea name="description" rows="4" placeholder="Írj valamit a buliról..."
                                      class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                                🚀 Event Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>