<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Détails de l\'enseignant') }}: {{ $teacher->name }}
            </h2>
            <div>
                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-primary">
                    <i class="fas fa-edit mr-2"></i> Modifier
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations personnelles</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Nom complet</p>
                                    <p class="mt-1">{{ $teacher->name }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Identifiant</p>
                                    <p class="mt-1">{{ $teacher->identifier }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p class="mt-1">{{ $teacher->email }}</p>
                                </div>
                                
                                @if($teacher->date_of_birth)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date de naissance</p>
                                    <p class="mt-1">{{ $teacher->date_of_birth->format('d/m/Y') }}</p>
                                </div>
                                @endif
                                
                                @if($teacher->phone)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Téléphone</p>
                                    <p class="mt-1">{{ $teacher->phone }}</p>
                                </div>
                                @endif
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Statut</p>
                                    @if($teacher->status === \App\Models\User::STATUS_APPROVED)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approuvé
                                        </span>
                                    @elseif($teacher->status === \App\Models\User::STATUS_PENDING)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            En attente
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejeté
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($teacher->address)
                            <div class="mt-6">
                                <p class="text-sm font-medium text-gray-500">Adresse</p>
                                <p class="mt-1 whitespace-pre-line">{{ $teacher->address }}</p>
                            </div>
                            @endif
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Affectations</h3>
                            
                            @if($teacher->teacherAssignments->isEmpty())
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-chalkboard-teacher text-4xl mb-4"></i>
                                    <p>Aucune affectation pour le moment</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($teacher->teacherAssignments->groupBy('academicYear.name') as $year => $assignments)
                                        <div>
                                            <h4 class="font-medium text-gray-700">{{ $year }}</h4>
                                            <div class="mt-2 space-y-2">
                                                @foreach($assignments as $assignment)
                                                    <div class="p-3 bg-gray-50 rounded-md">
                                                        <div class="flex justify-between items-start">
                                                            <div>
                                                                <p class="font-medium">{{ $assignment->subject->name }}</p>
                                                                <p class="text-sm text-gray-600">{{ $assignment->schoolClass->name }}</p>
                                                            </div>
                                                            <form action="{{ route('admin.teachers.assignments.destroy', $assignment) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-800" 
                                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette affectation ?')">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-6">
                                    <a href="{{ route('admin.teachers.assignments.create', $teacher) }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-2"></i> Ajouter une affectation
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
