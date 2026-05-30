@props([
    'name',
    'id' => null,
    'accept' => null,
    'required' => false,
    'button' => 'Escolher arquivo',
    'placeholder' => 'Nenhum arquivo selecionado',
])

@php
    $inputId = $id ?: 'nexo-file-'.str_replace(['[', ']'], '-', $name).'-'.uniqid();
@endphp

<div {{ $attributes->class('nexo-file-picker') }}>
    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="file"
        class="nexo-file-picker-input"
        @if($accept) accept="{{ $accept }}" @endif
        @if($required) required @endif
    >

    <label class="nexo-file-picker-button" for="{{ $inputId }}">
        {{ $button }}
    </label>

    <span class="nexo-file-picker-name" data-nexo-file-name-for="{{ $inputId }}">
        {{ $placeholder }}
    </span>
</div>

@once
    <style>
        .nexo-file-picker {
            position: relative;
            min-height: 50px;
            display: flex;
            align-items: stretch;
            overflow: hidden;
            border: 1px solid #D8E2EF;
            border-radius: 13px;
            background: #FFFFFF;
        }

        .nexo-file-picker-input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .nexo-file-picker-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 168px;
            padding: 0 16px;
            margin: 0;
            border-right: 1px solid #E4EBF5;
            background: #F8FAFC;
            color: #061C3F;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .nexo-file-picker-button:hover {
            background: #EEF4FB;
        }

        .nexo-file-picker-name {
            min-width: 0;
            display: flex;
            align-items: center;
            flex: 1;
            padding: 0 16px;
            color: #162033;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nexo-file-picker:focus-within {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }
    </style>
@endonce

<script>
    (() => {
        const input = document.getElementById(@json($inputId));
        const fileName = document.querySelector('[data-nexo-file-name-for="' + @json($inputId) + '"]');

        input?.addEventListener('change', () => {
            fileName.textContent = input.files && input.files.length
                ? input.files[0].name
                : @json($placeholder);
        });
    })();
</script>
