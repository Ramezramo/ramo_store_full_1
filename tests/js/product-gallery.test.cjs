const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { runInNewContext } = require('node:vm');
const context = { module: { exports: {} } };
runInNewContext(readFileSync(resolve(__dirname, '../../public/js/product-gallery.js'), 'utf8'), context);
const { imageLoader } = context.module.exports;

function fixture() {
  const images = [], shown = [], failures = [], states = [];
  const select = imageLoader({ createImage: () => { const image = {}; images.push(image); return image; },
    ready: url => shown.push(url), failed: url => failures.push(url), busy: value => states.push(value) });
  return { select, images, shown, failures, states };
}

test('keeps the displayed image until the replacement has loaded and decoded', async () => {
  const gallery = fixture();
  gallery.select('second.jpg');
  assert.deepEqual(gallery.shown, []);
  let decode;
  gallery.images[0].decode = () => new Promise(resolve => { decode = resolve; });
  const completed = gallery.images[0].onload();
  assert.deepEqual(gallery.shown, []);
  decode(); await completed;
  assert.deepEqual(gallery.shown, ['second.jpg']);
  assert.deepEqual(gallery.states, [true, false]);
});

test('rapid next/previous or color changes cannot be overwritten by a late image', async () => {
  const gallery = fixture();
  gallery.select('red.jpg'); gallery.select('blue.jpg');
  await gallery.images[1].onload();
  await gallery.images[0].onload();
  gallery.images[0].onerror();
  assert.deepEqual(gallery.shown, ['blue.jpg']);
  assert.deepEqual(gallery.failures, []);
  assert.deepEqual(gallery.states, [true, true, false]);
});

test('a missing image ends the busy state and preserves the previous picture', () => {
  const gallery = fixture();
  gallery.select('missing.jpg'); gallery.images[0].onerror();
  assert.deepEqual(gallery.shown, []);
  assert.deepEqual(gallery.failures, ['missing.jpg']);
  assert.deepEqual(gallery.states, [true, false]);
});
