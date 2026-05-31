@props([
    'name',
    'id' => null,
    'accept' => null,
    'required' => false,
    'multiple' => false,
    'button' => 'Escolher arquivo',
    'placeholder' => 'Nenhum arquivo selecionado',
])

@php
    $inputId = $id ?: 'nexo-file-'.str_replace(['[', ']'], '-', $name).'-'.uniqid();
@endphp

<label {{ $attributes->class('nexo-file-picker nexo-file-upload') }}>
    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="file"
        class="nexo-file-picker-input nexo-file-input"
        @if($accept) accept="{{ $accept }}" @endif
        @if($required) required @endif
        @if($multiple) multiple="multiple" @endif
    >

    <span class="nexo-file-picker-icon nexo-file-icon">
        <i class="bi bi-cloud-arrow-up"></i>
    </span>

    <span class="nexo-file-picker-content nexo-file-content">
        <strong class="nexo-file-picker-title nexo-file-title">{{ $button }}</strong>
        <small class="nexo-file-picker-name nexo-file-name" data-nexo-file-name-for="{{ $inputId }}">
            {{ $placeholder }}
        </small>
    </span>
</label>

@once
    <style>
        .nexo-file-picker {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 74px;
            padding: 16px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px dashed #BFD7F8;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .nexo-file-picker-input {
            display: none;
        }

        .nexo-file-picker-icon {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 12px 26px rgba(47, 128, 237, 0.20);
        }

        .nexo-file-picker:hover {
            background: #F1F7FF;
            border-color: #2F80ED;
        }

        .nexo-file-picker-content {
            min-width: 0;
            display: grid;
            gap: 2px;
            flex: 1;
        }

        .nexo-file-picker-title {
            color: #061C3F;
            font-weight: 950;
        }

        .nexo-file-picker-name {
            color: #162033;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            overflow-wrap: anywhere;
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
        let selectedFiles = [];

        const fileKey = (file) => [
            file.name,
            file.size,
            file.type,
            file.lastModified,
        ].join(':');

        const syncInputFiles = () => {
            if (! input?.multiple || typeof DataTransfer === 'undefined') {
                return;
            }

            const transfer = new DataTransfer();
            selectedFiles.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
        };

        const renderFileNames = () => {
            if (! fileName) {
                return;
            }

            const files = Array.from(input?.files || []);
            fileName.textContent = files.length
                ? files.map((file) => file.name).join(', ')
                : @json($placeholder);
        };

        input?.addEventListener('change', () => {
            if (input.multiple && typeof DataTransfer !== 'undefined') {
                const knownFiles = new Set(selectedFiles.map(fileKey));

                Array.from(input.files || []).forEach((file) => {
                    const key = fileKey(file);

                    if (! knownFiles.has(key)) {
                        selectedFiles.push(file);
                        knownFiles.add(key);
                    }
                });

                syncInputFiles();
            }

            renderFileNames();
        });
    })();
</script>
