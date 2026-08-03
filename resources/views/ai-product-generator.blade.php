@extends('layouts.app')

@section('title', 'Trispark - AI Product Generator')

@section('content')
<section class="section ai-generator-page" style="padding-top:150px;">
    <div class="section-header">
        <div class="section-badge">AI Product Generator</div>
        <h2>Generate Product Listing Content</h2>
        <p class="section-subtitle">Upload a product image and generate editable SEO-friendly title, description, category, features, and tags.</p>
    </div>

    <div class="detail-actions" style="justify-content:space-between; margin-bottom:18px;">
        <a href="{{ route('data.index') }}" class="btn-secondary" style="text-decoration:none;display:inline-flex;">Back to Admin</a>
        <form method="POST" action="{{ route('data.logout') }}">
            @csrf
            <button type="submit" class="btn-secondary">Logout</button>
        </form>
    </div>

    <div class="ai-generator-layout">
        <form id="aiProductForm" class="detail-card booking-form ai-generator-upload-card">
            @csrf
            <label class="ai-upload-zone" for="productImage">
                <input id="productImage" type="file" name="product_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                <span class="ai-upload-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 16V4m0 0l-4 4m4-4l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 16.5V18a2 2 0 002 2h12a2 2 0 002-2v-1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <strong>Upload product image</strong>
                <span>JPG, JPEG, PNG, WEBP up to 5MB</span>
            </label>

            <div class="ai-preview-frame" id="previewFrame" hidden>
                <img id="imagePreview" src="" alt="Selected product preview">
            </div>

            <button id="generateButton" type="submit" class="btn-primary ai-generate-button">
                Generate Title & Description
            </button>

            <div id="aiLoading" class="ai-loading" hidden>
                <span class="ai-spinner"></span>
                <span>Analyzing image with Gemini Vision...</span>
            </div>

            <p id="aiError" class="ai-error" hidden></p>
            <pre id="aiDebug" class="ai-debug-output" hidden></pre>
        </form>

        <div class="detail-card booking-form ai-generator-result-card">
            <div class="form-grid">
                <input id="resultTitle" type="text" placeholder="Product Title">
                <textarea id="resultShortDescription" placeholder="Short Description"></textarea>
                <textarea id="resultDescription" placeholder="Full Description"></textarea>
                <input id="resultCategory" type="text" placeholder="Category">
                <textarea id="resultFeatures" placeholder="Features, one per line"></textarea>
                <textarea id="resultTags" placeholder="Tags, comma separated"></textarea>
            </div>
        </div>
    </div>
</section>

<script>
    const aiProductForm = document.getElementById('aiProductForm');
    const productImage = document.getElementById('productImage');
    const previewFrame = document.getElementById('previewFrame');
    const imagePreview = document.getElementById('imagePreview');
    const generateButton = document.getElementById('generateButton');
    const aiLoading = document.getElementById('aiLoading');
    const aiError = document.getElementById('aiError');
    const aiDebug = document.getElementById('aiDebug');

    const resultFields = {
        title: document.getElementById('resultTitle'),
        short_description: document.getElementById('resultShortDescription'),
        description: document.getElementById('resultDescription'),
        category: document.getElementById('resultCategory'),
        features: document.getElementById('resultFeatures'),
        tags: document.getElementById('resultTags'),
    };
    const maxSelectableSize = 5 * 1024 * 1024;
    const maxUploadPayloadSize = 1800 * 1024;

    const showError = (message, debug = null) => {
        aiError.textContent = message;
        aiError.hidden = false;

        if (debug) {
            aiDebug.textContent = JSON.stringify(debug, null, 2);
            aiDebug.hidden = false;
        }
    };

    const clearError = () => {
        aiError.textContent = '';
        aiError.hidden = true;
        aiDebug.textContent = '';
        aiDebug.hidden = true;
    };

    productImage?.addEventListener('change', () => {
        clearError();
        const file = productImage.files?.[0];
        if (!file) {
            previewFrame.hidden = true;
            imagePreview.src = '';
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            productImage.value = '';
            previewFrame.hidden = true;
            showError('Invalid image type. Please upload JPG, JPEG, PNG, or WEBP.');
            return;
        }

        if (file.size > maxSelectableSize) {
            productImage.value = '';
            previewFrame.hidden = true;
            showError('Image is too large. Maximum allowed size is 5MB.');
            return;
        }

        imagePreview.src = URL.createObjectURL(file);
        previewFrame.hidden = false;
    });

    const canvasToBlob = (canvas, type, quality) => new Promise((resolve) => {
        canvas.toBlob(resolve, type, quality);
    });

    const prepareImageForUpload = async (file) => {
        if (file.size <= maxUploadPayloadSize) {
            return file;
        }

        const image = new Image();
        const objectUrl = URL.createObjectURL(file);
        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
            image.src = objectUrl;
        });
        URL.revokeObjectURL(objectUrl);

        const maxDimension = 1600;
        const ratio = Math.min(1, maxDimension / Math.max(image.width, image.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.width * ratio));
        canvas.height = Math.max(1, Math.round(image.height * ratio));

        const context = canvas.getContext('2d');
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        const qualities = [0.86, 0.78, 0.68, 0.58];
        for (const quality of qualities) {
            const blob = await canvasToBlob(canvas, 'image/jpeg', quality);
            if (blob && blob.size <= maxUploadPayloadSize) {
                return new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' });
            }
        }

        throw new Error('Prepared image is still too large. Please use a smaller image.');
    };

    aiProductForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError();

        if (!productImage.files?.length) {
            showError('Please upload a product image first.');
            return;
        }

        generateButton.disabled = true;
        aiLoading.hidden = false;

        try {
            const preparedFile = await prepareImageForUpload(productImage.files[0]);
            const formData = new FormData();
            formData.append('product_image', preparedFile);

            const response = await fetch('{{ route('data.ai-product-generator.generate') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const payload = await response.json();
            if (!response.ok) {
                const validationMessage = payload.errors
                    ? Object.values(payload.errors).flat().join(' ')
                    : payload.message;
                showError(validationMessage || 'Unable to generate product content.', payload.gemini_debug || null);
                return;
            }

            const result = payload.result || {};
            resultFields.title.value = result.title || '';
            resultFields.short_description.value = result.short_description || '';
            resultFields.description.value = result.description || '';
            resultFields.category.value = result.category || '';
            resultFields.features.value = Array.isArray(result.features) ? result.features.join('\n') : '';
            resultFields.tags.value = Array.isArray(result.tags) ? result.tags.join(', ') : '';
        } catch (error) {
            showError(error.message || 'Request failed. Please check your connection and try again.');
        } finally {
            generateButton.disabled = false;
            aiLoading.hidden = true;
        }
    });
</script>
@endsection
