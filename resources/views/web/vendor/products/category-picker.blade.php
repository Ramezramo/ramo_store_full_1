@once
  @push('styles')
    <link rel="stylesheet" href="{{ asset('css/vendor-category-picker.css') }}?v={{ filemtime(public_path('css/vendor-category-picker.css')) }}">
    <script src="{{ asset('js/vendor-category-picker.js') }}?v={{ filemtime(public_path('js/vendor-category-picker.js')) }}" defer></script>
  @endpush
@endonce

@php
  $categoryNames = $categories->pluck('name', 'id');
  $categoryChoices = $categories->sortBy(fn ($category) =>
    ($categoryNames->get($category->parent) ? $categoryNames->get($category->parent) . ' / ' : '') . $category->name,
    SORT_NATURAL | SORT_FLAG_CASE
  );
  $pickedCategoryIds = (array) old('categories', old('category_picker_present') ? [] : $selectedCats);
@endphp

<div class="category-picker" data-category-picker>
  <input type="hidden" name="category_picker_present" value="1">
  @if($categoryChoices->isEmpty())
    <p class="category-picker-note">No categories available.</p>
  @else
    <div class="category-picker-controls" data-category-controls hidden>
      <input class="vs-input" type="search" aria-label="Search categories" placeholder="Search categories…"
             autocomplete="off" enterkeyhint="search" data-category-query>
      <button class="vs-btn vs-btn-ghost" type="button" aria-controls="product-category-list" aria-expanded="false" data-category-browse>View all</button>
    </div>
    <p class="category-picker-note" role="status" data-category-count hidden></p>
    <p class="category-picker-note" role="status" data-category-empty hidden>No matching categories.</p>

    <div class="category-picker-list" id="product-category-list" aria-label="Product categories" role="group">
      @foreach($categoryChoices as $category)
        @php($parentName = $categoryNames->get($category->parent))
        <label class="category-picker-option" data-category-option>
          <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $pickedCategoryIds))>
          <span>
            <span data-category-name>{{ $category->name }}</span>
            @if($parentName)<small>{{ $parentName }}</small>@endif
          </span>
        </label>
      @endforeach
    </div>
  @endif
</div>
