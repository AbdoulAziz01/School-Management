<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier l\'enseignant') }}: {{ $teacher->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Informations personnelles</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-label for="name" :value="__('Nom complet')" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" 
                                             :value="old('name', $teacher->name)" required autofocus />
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="identifier" :value="__('Identifiant')" />
                                    <x-input id="identifier" class="block mt-1 w-full" type="text" 
                                             name="identifier" :value="old('identifier', $teacher->identifier)" required />
                                    @error('identifier')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="email" :value="__('Email')" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" 
                                             name="email" :value="old('email', $teacher->email)" required />
                                    @error('email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="date_of_birth" :value="__('Date de naissance')" />
                                    <x-input id="date_of_birth" class="block mt-1 w-full" type="date" 
                                             name="date_of_birth" :value="old('date_of_birth', $teacher->date_of_birth ? $teacher->date_of_birth->format('Y-m-d') : '')" />
                                    @error('date_of_birth')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="phone" :value="__('Téléphone')" />
                                    <x-input id="phone" class="block mt-1 w-full" type="text" 
                                             name="phone" :value="old('phone', $teacher->phone)" />
                                    @error('phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="status" :value="__('Statut')" />
                                    <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="pending" {{ old('status', $teacher->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="approved" {{ old('status', $teacher->status) === 'approved' ? 'selected' : '' }}>Approuvé</option>
                                        <option value="rejected" {{ old('status', $teacher->status) === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <x-label for="address" :value="__('Adresse')" />
                                    <x-textarea id="address" class="block mt-1 w-full" name="address">
                                        {{ old('address', $teacher->address) }}
                                    </x-textarea>
                                    @error('address')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Changer le mot de passe</h3>
                            <p class="text-sm text-gray-600 mb-4">Laissez ces champs vides pour conserver le mot de passe actuel.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-label for="password" :value="__('Nouveau mot de passe')" />
                                    <x-input id="password" class="block mt-1 w-full"
                                             type="password"
                                             name="password"
                                             autocomplete="new-password" />
                                    @error('password')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-label for="password_confirmation" :value="__('Confirmer le nouveau mot de passe')" />
                                    <x-input id="password_confirmation" class="block mt-1 w-full"
                                             type="password"
                                             name="password_confirmation" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-8">
                            <div>
                                <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-secondary mr-4">
                                    <i class="fas fa-arrow-left mr-2"></i> Retour
                                </a>
                            </div>
                            <div>
                                <button type="button" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?')) { document.getElementById('delete-form').submit(); }" 
                                        class="btn btn-danger mr-4">
                                    <i class="fas fa-trash mr-2"></i> Supprimer
                                </button>
                                
                                <x-button class="bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                                </x-button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
