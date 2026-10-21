const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');

const source = fs.readFileSync(require('node:path').join(__dirname, '..', 'photo_game', 'sw.js'), 'utf8');

function createWorker() {
    const handlers = {};
    const puts = [];
    const cache = { addAll: async () => {}, put: async (request) => { puts.push(request.url); } };
    const keysDeleted = [];
    const requests = [];
    const caches = {
        open: async () => cache,
        match: async () => undefined,
        keys: async () => ['photo-challenge-v2', 'photo-challenge-v3'],
        delete: async key => { keysDeleted.push(key); return true; }
    };
    const self = {
        registration: { scope: 'https://ssp.example/photo_game/' },
        location: { origin: 'https://ssp.example' },
        addEventListener: (name, handler) => { handlers[name] = handler; },
        skipWaiting: async () => {},
        clients: { claim: async () => {} }
    };
    const fetch = async request => {
        requests.push(request.url);
        return { ok: true, type: 'basic', clone() { return this; } };
    };
    vm.runInNewContext(source, { self, caches, fetch, URL });
    return { handlers, puts, requests, keysDeleted };
}

function dispatchFetch(worker, path, method = 'GET') {
    const pending = [];
    const event = {
        request: { url: 'https://ssp.example' + path, method },
        waitUntil(promise) { pending.push(promise); },
        respondWith(promise) { this.response = promise; },
        response: null
    };
    worker.handlers.fetch(event);
    return { event, drain: async () => { if (event.response) await event.response; await Promise.all(pending); } };
}

test('only explicit static assets are cached, not uploads or other images', async () => {
    const w = createWorker();
    for (const path of ['/photo_game/uploads/participant.png', '/photo_game/gallery.php', '/photo_game/graphics/other.jpg']) {
        const x = dispatchFetch(w, path);
        assert.equal(x.event.response, null, path);
        await x.drain();
    }
    const x = dispatchFetch(w, '/photo_game/style.css');
    assert.ok(x.event.response);
    await x.drain();
    assert.deepEqual(w.puts, ['https://ssp.example/photo_game/style.css']);
});

test('non-GET requests bypass service worker cache', () => {
    const w = createWorker();
    assert.equal(dispatchFetch(w, '/photo_game/style.css', 'POST').event.response, null);
});

test('activation removes previous cache, including any previously cached photos', async () => {
    const w = createWorker();
    const pending = [];
    w.handlers.activate({ waitUntil: p => pending.push(p) });
    await Promise.all(pending);
    assert.deepEqual(w.keysDeleted, ['photo-challenge-v2']);
});
