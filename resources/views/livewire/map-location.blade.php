<div class="container-fluid">
    
    <div class="row">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header bg-dark text-white">
                    Peta Lokasi Kuliner
                </div>
                <div class="card-body">
                    <div wire:ignore id='map' style='width: 100%; height: 75vh;'></div>
                </div>
            </div>
            
        </div>

        <div class="col-md-4">

            <div class="card">
                <div class="card-header bg-dark text-white">
                    Form
                </div>
                <div class="card-body">
                    <form 
                        @if($isEdit)
                            wire:submit.prevent="updateLocation"
                        @else
                            wire:submit.prevent="saveLocation"
                        @endif
                    >
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input wire:model="long" type="text" class="form-control">
                                    @error('long') <small class="text-danger">{{$message}}</small>@enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Latitude</label>
                                    <input wire:model="lat" type="text" class="form-control">
                                    @error('lat') <small class="text-danger">{{$message}}</small>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input wire:model="title" type="text" class="form-control">
                            @error('title') <small class="text-danger">{{$message}}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea wire:model="description" class="form-control"></textarea>
                            @error('description') <small class="text-danger">{{$message}}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label>Picture</label>
                            <input wire:model="image" type="file" class="form-control">
                            @error('image') <small class="text-danger">{{$message}}</small>@enderror
                            @if($image)
                                 <img src="{{$image->temporaryUrl()}}" class="img-fluid">
                            @endif

                            @if($imageUrl && !$image)
                                 <img src="{{asset('/storage/images/'.$imageUrl)}}" class="img-fluid">
                            @endif

                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-dark text-white mt-1 w-100">{{$isEdit ? "Update Location" : "Submit Location"}}</button>
                            @if($isEdit)
                            <button wire:click="deleteLocation" type="button" class="btn btn-danger text-white mt-1 w-100">Delete Location</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">

    <script>
        document.addEventListener('livewire:load', () => {
            const defaultLocation = [106.89168840013895, -6.180490850021073]

            mapboxgl.accessToken = '{{env('MAPBOX_KEY')}}';
            var map = new mapboxgl.Map({
                container: 'map',
                center: defaultLocation,
                zoom: 13,

                // jenis style : streets-v11, light-v10, outdoors-v11, satellite-v9, dark-v10
                style: 'mapbox://styles/mapbox/streets-v11'
            });;

            // Add the control to the map.
            map.addControl(
                new MapboxGeocoder({
                    accessToken: mapboxgl.accessToken,
                    mapboxgl: mapboxgl
                })
            );

            const loadLocations = (geoJson) => {
                geoJson.features.forEach((location) => {
                    const {geometry, properties} = location
                    const {iconSize, locationId, title, image, description} = properties

                    let markerElement = document.createElement('div')
                    markerElement.className = 'marker' + locationId
                    markerElement.id = locationId
                    markerElement.style.backgroundImage = 'url(https://cdn-icons-png.flaticon.com/512/1147/1147805.png?w=360)'
                    markerElement.style.backgroundSize = 'cover'
                    markerElement.style.width = '50px'
                    markerElement.style.height = '50px'

                    const imageStorage = '{{asset("/storage/images")}}' + '/' + image 

                    const content = `
                        <div style="overflow-y, auto;max-height:400px,width:100%">
                            <table class="table table-sm mt-2">
                                <tbody>
                                    <tr>
                                        <td>Title</td>
                                        <td>${title}</td>
                                    </tr>
                                    <tr>
                                        <td>Image</td>
                                        <td><img src="${imageStorage}" loading="lazy" class="img-fluid"></td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td>${description}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `

                    markerElement.addEventListener('click', (e) => {
                            const locationId = e.target.id
                            @this.findLocationById(locationId)
                        }
                    )

                    const popUp = new mapboxgl.Popup({
                        offset:25
                    }).setHTML(content).setMaxWidth("400px")

                    new mapboxgl.Marker(markerElement)
                    .setLngLat(geometry.coordinates)
                    .setPopup(popUp)
                    .addTo(map)

                })
            }

            loadLocations({!! $geoJson !!})

            window.addEventListener('locationAdded', (e) =>{
                loadLocations(JSON.parse(e.detail))
            })

            window.addEventListener('updateLocation', (e) =>{
                loadLocations(JSON.parse(e.detail))
                //window.location.reload()
                $('.mapboxgl-popup').remove()
            })

            window.addEventListener('deleteLocation', (e) =>{
                $('.marker' + e.detail).remove()
                $('.mapboxgl-popup').remove()
                //window.location.reload()
            })

            map.addControl(new mapboxgl.NavigationControl())

            map.on('click', (e) => {
                const longitude = e.lngLat.lng
                const latitude = e.lngLat.lat

                //console.log({longitude, latitude});
                @this.long = longitude
                @this.lat = latitude
            })
        })
    </script>

@endpush