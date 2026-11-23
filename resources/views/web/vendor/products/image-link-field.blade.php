@php
  $restoreLink = $useOld ?? true;
  $linkValue = $restoreLink ? old($name) : null;
  $linkError = $restoreLink ? $errors->first($name) : '';
@endphp
<details class="vs-form-group" style="margin-top:10px;margin-bottom:0" @if($linkError || is_string($linkValue) && $linkValue !== '') open @endif>
  <summary class="product-image-link-toggle" style="cursor:pointer;font-weight:600">Use image {{ empty($multiple) ? 'link' : 'links' }}</summary>
  <label class="vs-label" for="{{ $id }}" style="display:block;margin:8px 0 4px">{{ $label }}</label>
  @if(empty($multiple))
    <input type="url" id="{{ $id }}" name="{{ $name }}" class="vs-input" maxlength="2048"
           value="{{ is_string($linkValue) ? $linkValue : '' }}" placeholder="https://images.example.com/photo.jpg">
  @else
    <textarea id="{{ $id }}" name="{{ $name }}" class="vs-input" rows="2" maxlength="65535"
              placeholder="One HTTPS image link per line">{{ is_string($linkValue) ? $linkValue : '' }}</textarea>
  @endif
  @if($linkError)<div class="vs-err" role="alert">{{ $linkError }}</div>@endif
</details>
