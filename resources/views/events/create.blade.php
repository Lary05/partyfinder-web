<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-700">
                
                <div class="p-8 border-b border-gray-700 bg-gray-900">
                    <h2 class="text-2xl font-bold text-white">✨ {{ __('Creating a New Party') }}</h2>
                    <p class="text-gray-400 text-sm mt-1">{{ __('Fill in the details and publish the event!') }}</p>
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

                    <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="locationPicker()">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">{{ __('Title') }}</label>
                            <input type="text" name="title" placeholder="{{ __('No title') }}" required
                                      class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">{{ __('Cover Image') }}</label>
                            <div class="flex items-center bg-gray-900 border border-gray-600 rounded-lg p-2">
                                <input type="file" id="image_upload" name="image" accept="image/*" class="hidden" 
                                       onchange="document.getElementById('image-file-name').textContent = this.files[0] ? this.files[0].name : '{{ __('No file chosen') }}'">
                                <label for="image_upload" class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 rounded-full text-white font-semibold text-sm hover:bg-blue-700 transition">
                                    {{ __('Choose File') }}
                                </label>
                                <span id="image-file-name" class="ml-4 text-sm text-gray-400">{{ __('No file chosen') }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">{{ __('Start Date') }}</label>
                            <input type="datetime-local" name="start_time" required
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500 [color-scheme:dark]">
                        </div>

                        <div class="bg-gray-900/50 p-5 rounded-xl border border-gray-700">
                            
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                                <label class="block text-sm font-bold text-gray-300">{{ __('Location') }}</label>
                                <label class="flex items-center cursor-pointer text-sm text-blue-400 hover:text-blue-300 transition font-bold bg-blue-900/20 px-3 py-1.5 rounded-lg border border-blue-800">
                                    <input type="checkbox" x-model="isNewLocation" @change="toggleNewLocation" class="form-checkbox h-4 w-4 text-blue-600 bg-gray-900 border-gray-700 rounded focus:ring-blue-500 mr-2">
                                    {{ __('Not in the list? Add a new one!') }}
                                </label>
                            </div>

                            <div x-show="!isNewLocation">
                                <select name="location_id" :required="!isNewLocation" class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>{{ __('Choose a location...') }}</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">
                                            {{ $location->name }} ({{ $location->city->name ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="isNewLocation" class="space-y-4 pt-2" style="display: none;">
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1">{{ __('Club / Location Name') }} *</label>
                                    <input type="text" name="new_location_name" :required="isNewLocation" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1">{{ __('City') }} *</label>
                                        <input type="text" id="new_city_name" name="new_city_name" x-model="newCity" list="city-options" autocomplete="off"
                                               class="w-full bg-gray-800 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500" 
                                               placeholder="{{ __('e.g. Budapest') }}">
                                        <datalist id="city-options">
                                            @foreach($cities as $city)
                                                <option value="{{ $city->name }}"></option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1">{{ __('Street, House Number') }}</label>
                                        <div class="flex">
                                            <input type="text" x-model="newAddress" name="new_location_address" class="w-full bg-gray-800 border border-gray-700 rounded-l-lg p-3 text-white focus:ring-2 focus:ring-blue-500" placeholder="{{ __('e.g. Váci út 1.') }}">
                                            <button type="button" @click="findLocation()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 rounded-r-lg font-bold transition">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="mapVisible" class="mt-4 transition-all duration-500">
                                    <label class="block text-xs font-medium text-gray-400 mb-1">{{ __('Pinpoint the exact location on the map') }}</label>
                                    <div id="location-map" class="w-full rounded-lg border border-gray-700 z-0 mb-4" style="height: 300px;"></div>
                                    
                                    <div x-show="!confirmed" class="bg-blue-900/30 border border-blue-500 p-4 rounded-lg flex flex-col sm:flex-row justify-between items-center gap-3">
                                        <span class="text-blue-200 text-sm font-medium">📍 {{ __('Is this the correct spot?') }}</span>
                                        <button type="button" @click="confirmLocation()" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded-lg font-bold text-sm transition shadow-lg">
                                            {{ __('Yes, looks good!') }}
                                        </button>
                                    </div>

                                    <div x-show="confirmed" class="bg-green-900/30 border border-green-500 p-4 rounded-lg text-green-400 text-sm font-bold flex items-center">
                                        <i class="fas fa-check-circle mr-2 text-lg"></i> {{ __('Location fixed!') }}
                                    </div>
                                </div>

                                <input type="hidden" name="new_location_lat" id="new_lat" :value="lat">
                                <input type="hidden" name="new_location_lng" id="new_lng" :value="lng">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">{{ __('Description') }}</label>
                            <textarea name="description" rows="4" placeholder="{{ __('Write something about the party...') }}"
                                      class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" 
                                    :disabled="isNewLocation && !confirmed"
                                    class="w-full font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]"
                                    :class="(isNewLocation && !confirmed) ? 'bg-gray-600 text-gray-400 cursor-not-allowed opacity-70' : 'bg-blue-600 hover:bg-blue-500 text-white'">
                                🚀 <span x-text="(isNewLocation && !confirmed) ? '{{ __('Please confirm the location first!') }}' : '{{ __('Event Announcement') }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function locationPicker() {
            return {
                isNewLocation: false,
                mapVisible: false,
                confirmed: false,
                map: null,
                marker: null,
                newCity: '',
                newAddress: '',
                lat: 47.4979,
                lng: 19.0402,

                toggleNewLocation() {
                    if(!this.isNewLocation) {
                        this.mapVisible = false;
                        this.confirmed = false;
                    }
                },

                findLocation() {
                    if(!this.newCity) return alert('{{ __("Add city and address first!") }}');
                    
                    let query = this.newCity + (this.newAddress ? ', ' + this.newAddress : '');
                    
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                        .then(r => r.json())
                        .then(data => {
                            this.mapVisible = true;
                            this.confirmed = false;

                            if(data.length > 0) {
                                this.lat = data[0].lat;
                                this.lng = data[0].lon;
                                this.showOnMap();
                            } else {
                                alert('{{ __("Not found. Try moving the pin manually!") }}');
                                this.showOnMap();
                            }
                        });
                },

                showOnMap() {
                    setTimeout(() => {
                        if (!this.map) {
                            this.map = L.map('location-map').setView([this.lat, this.lng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap'
                            }).addTo(this.map);
                            
                            this.marker = L.marker([this.lat, this.lng], {draggable: true}).addTo(this.map);
                            
                            this.marker.on('dragend', () => {
                                let pos = this.marker.getLatLng();
                                this.lat = pos.lat;
                                this.lng = pos.lng;
                                this.confirmed = false; // Ha elmozdítja a tűt, újra jóvá kell hagynia!
                            });
                        } else {
                            this.map.setView([this.lat, this.lng], 15);
                            this.marker.setLatLng([this.lat, this.lng]);
                        }
                        this.map.invalidateSize();
                    }, 200);
                },

                confirmLocation() {
                    this.confirmed = true;
                }
            }
        }
    </script>
</x-app-layout>