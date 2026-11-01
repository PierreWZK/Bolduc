<x-layout titre="Nouvelle liste" :noindex="true">
    <x-formulaire titre="Une liste, <em>pour quelle fête ?</em>" :action="route('listes.store')" bouton="Créer la liste">
        <x-champ nom="title" libelle="Le titre" aide="Par exemple « Noël chez Camille »." required autofocus />

        <div class="champ">
            <label for="event_type">L'événement</label>
            <select id="event_type" name="event_type" required @error('event_type') aria-invalid="true" aria-describedby="event_type-erreur" @enderror>
                @foreach ($evenements as $cle => $nom)
                    <option value="{{ $cle }}" @selected(old('event_type') === $cle)>{{ $nom }}</option>
                @endforeach
            </select>
            @error('event_type')
                <p class="erreur" id="event_type-erreur">{{ $message }}</p>
            @enderror
        </div>

        <x-champ nom="event_date" libelle="La date (facultatif)" type="date" aide="Pour le compte à rebours." />
        <x-champ nom="coproprietaire" libelle="Un co-propriétaire (facultatif)" type="email" aide="Son e-mail, s'il a déjà un compte Bolduc. Il gérera la liste avec toi." />
    </x-formulaire>
</x-layout>
