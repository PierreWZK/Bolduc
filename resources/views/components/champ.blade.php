@props(['nom', 'libelle', 'type' => 'text', 'aide' => null])
<div class="champ">
    <label for="{{ $nom }}">{{ $libelle }}</label>
    <input id="{{ $nom }}" name="{{ $nom }}" type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($nom, $attributes->get('value')) }}" @endif
        @error($nom) aria-invalid="true" aria-describedby="{{ $nom }}-erreur" @enderror
        {{ $attributes->except('value') }}>
    @error($nom)
        <p class="erreur" id="{{ $nom }}-erreur">{{ $message }}</p>
    @else
        @if ($aide) <p class="aide">{{ $aide }}</p> @endif
    @enderror
</div>
