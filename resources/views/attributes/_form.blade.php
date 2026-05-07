{{-- Shared form partial used by create.blade.php and edit.blade.php --}}
{{-- Expected vars: $attribute (Attribute|null), $action (string), $method (POST|PUT) --}}

@push('styles')
<style>
.attr-values-box {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    min-height: 48px;
    border: 1px solid var(--input);
    border-radius: 8px;
    padding: 8px 10px;
    background: var(--background);
}
.attr-values-box input {
    border: none;
    outline: none;
    font-size: 0.9375rem;
    color: var(--foreground);
    background: transparent;
    flex: 1;
    min-width: 120px;
    padding: 4px 0;
}
.value-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--muted);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 0.875rem;
    color: var(--foreground);
}
.value-chip .x {
    cursor: pointer;
    color: var(--muted-foreground);
    line-height: 1;
    font-size: 0.875rem;
}
.value-chip .x:hover {
    color: var(--destructive);
}
</style>
@endpush

<form action="{{ $action }}" method="POST" id="attribute-form">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="card" style="max-width:780px;">
        <div class="card-header">
            <h3 class="card-title">Attribute Details</h3>
        </div>
        <div class="card-body">

            {{-- Name --}}
            <div class="form-group">
                <label for="name" class="form-label">
                    Attribute Name <span style="color:var(--destructive);">*</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $attribute->name ?? '') }}"
                       placeholder="e.g. Size, Color, Material" required maxlength="255">
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
                <p class="form-hint">A short, descriptive name. Must be unique across all attributes.</p>
            </div>

            {{-- Values --}}
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Values</label>
                <div class="attr-values-box" id="values-box" onclick="document.getElementById('value-input').focus()">
                    @php
                        $existingValues = old('values', isset($attribute) ? $attribute->values->pluck('value')->toArray() : []);
                    @endphp
                    @foreach($existingValues as $val)
                        @if(trim($val) !== '')
                            <span class="value-chip">
                                <input type="hidden" name="values[]" value="{{ $val }}">
                                <span>{{ $val }}</span>
                                <span class="x" onclick="event.stopPropagation();this.closest('.value-chip').remove()">✕</span>
                            </span>
                        @endif
                    @endforeach
                    <input type="text" id="value-input" placeholder="Type a value and press Enter…" onkeydown="addValue(event)">
                </div>
                @error('values.*')<span class="form-error">{{ $message }}</span>@enderror
                <p class="form-hint">Press <kbd>Enter</kbd> or <kbd>,</kbd> to add. Click ✕ to remove.</p>
            </div>

        </div>
        <div class="card-footer" style="display:flex; gap:0.625rem; justify-content:flex-end;">
            <a href="{{ route('attributes.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ $method === 'PUT' ? 'Update Attribute' : 'Create Attribute' }}
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
function addValue(e) {
    if (e.key !== 'Enter' && e.key !== ',') return;
    e.preventDefault();
    const input = e.target;
    const val = input.value.trim();
    if (!val) return;

    // De-dupe (case-insensitive)
    const existing = [...document.querySelectorAll('#values-box input[name="values[]"]')]
        .map(i => i.value.toLowerCase());
    if (existing.includes(val.toLowerCase())) {
        input.value = '';
        return;
    }

    const chip = document.createElement('span');
    chip.className = 'value-chip';
    chip.innerHTML = `
        <input type="hidden" name="values[]" value="${val.replace(/"/g, '&quot;')}">
        <span>${val.replace(/</g, '&lt;')}</span>
        <span class="x" onclick="event.stopPropagation();this.closest('.value-chip').remove()">✕</span>`;
    input.parentNode.insertBefore(chip, input);
    input.value = '';
}
</script>
@endpush
