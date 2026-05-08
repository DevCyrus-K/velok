<?php

use App\Models\GalleryItem;

it('returns an empty alt text string for a new gallery item', function () {
    expect((new GalleryItem())->altText())->toBe('');
});
