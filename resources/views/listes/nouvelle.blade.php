{{-- Sert à créer une liste, et à modifier ses réglages quand $liste est fournie. --}}
@php($liste ??= null)
<x-layout :titre="$liste ? 'Réglages de la liste' : 'Nouvelle liste'" :noindex="true">
    <x-formulaire :titre="$liste ? 'Les réglages, <em>de ta liste</em>' : 'Une liste, <em>pour quelle fête ?</em>'" :action="$liste ? route('listes.update', $liste) : route('listes.store')" :bouton="$liste ? 'Enregistrer' : 'Créer la liste'">
        @if ($liste)
            @method('PUT')
        @endif
        <x-champ nom="title" libelle="Le titre" aide="Par exemple « Noël chez Camille »." :value="$liste?->title" required autofocus />

        <div class="champ">
            <label for="event_type">L'événement</label>
            <select id="event_type" name="event_type" required @error('event_type') aria-invalid="true" aria-describedby="event_type-erreur" @enderror>
                @foreach ($evenements as $cle => $nom)
                    <option value="{{ $cle }}" @selected(old('event_type', $liste?->event_type) === $cle)>{{ $nom }}</option>
                @endforeach
            </select>
            @error('event_type')
                <p class="erreur" id="event_type-erreur">{{ $message }}</p>
            @enderror
        </div>

        <x-champ nom="event_date" libelle="La date (facultatif)" type="date" aide="Pour le compte à rebours." :value="$liste?->event_date?->format('Y-m-d')" />
        @unless ($liste)
            <x-champ nom="coproprietaire" libelle="Un co-propriétaire (facultatif)" type="email" aide="Son e-mail, s'il a déjà un compte Bolduc. Il gérera la liste avec toi." />
        @endunless
    </x-formulaire>
</x-layout>
