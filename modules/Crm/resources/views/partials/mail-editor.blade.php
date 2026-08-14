@php
    $model = $model ?? 'compose_body';
    $editorId = $editorId ?? ('rte-'.str_replace('_', '-', $model));
    $placeholder = $placeholder ?? 'Write your message…';
    $showToolbar = $showToolbar ?? true;
@endphp
<div class="crm-rte" data-editor-id="{{ $editorId }}">
    @if($showToolbar)
        <div class="crm-rte-toolbar" role="toolbar" aria-label="Formatting">
            <button type="button" class="crm-rte-btn" data-rte-cmd="undo" title="Undo">↶</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="redo" title="Redo">↷</button>
            <span class="crm-rte-sep"></span>
            <select class="crm-rte-select" data-rte-select="fontName" title="Font">
                <option value="Arial">Sans Serif</option>
                <option value="Georgia">Serif</option>
                <option value="Courier New">Monospace</option>
            </select>
            <select class="crm-rte-select" data-rte-select="fontSize" title="Size">
                <option value="2">Small</option>
                <option value="3" selected>Normal</option>
                <option value="4">Large</option>
                <option value="5">Huge</option>
            </select>
            <span class="crm-rte-sep"></span>
            <button type="button" class="crm-rte-btn" data-rte-cmd="bold" title="Bold"><b>B</b></button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="italic" title="Italic"><i>I</i></button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="underline" title="Underline"><u>U</u></button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="strikeThrough" title="Strikethrough"><s>S</s></button>
            <label class="crm-rte-btn crm-rte-color" title="Text color">
                A
                <input type="color" value="#e8ecff" oninput="document.execCommand('foreColor', false, this.value)">
            </label>
            <span class="crm-rte-sep"></span>
            <button type="button" class="crm-rte-btn" data-rte-cmd="justifyLeft" title="Align left">≣</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="justifyCenter" title="Align center">≡</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="justifyRight" title="Align right">☰</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="insertUnorderedList" title="Bulleted list">•≡</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="insertOrderedList" title="Numbered list">1.</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="outdent" title="Decrease indent">⇤</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="indent" title="Increase indent">⇥</button>
            <span class="crm-rte-sep"></span>
            <button type="button" class="crm-rte-btn" data-rte-cmd="createLink" title="Insert link">🔗</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="insertImage" title="Insert image">🖼</button>
            <button type="button" class="crm-rte-btn" data-rte-cmd="removeFormat" title="Clear formatting">Tx</button>
        </div>
    @endif
    <div
        id="{{ $editorId }}"
        class="crm-rte-editor"
        contenteditable="true"
        data-model="{{ $model }}"
        data-placeholder="{{ $placeholder }}"
        wire:ignore
    >{!! $initialHtml ?? '' !!}</div>
</div>
