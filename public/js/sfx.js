// Tiny UI sound kit, synthesised with Web Audio (no audio files to download).
// Browsers only allow sound after the user interacts with the page, so the audio
// context is created on the first pointer/key press and reused afterwards.
(function () {
    var ctx = null;

    function ensure() {
        if (ctx) { if (ctx.state === 'suspended') ctx.resume(); return ctx; }
        var AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) return null;
        try { ctx = new AC(); } catch (e) { ctx = null; }
        return ctx;
    }
    ['pointerdown', 'keydown', 'touchstart'].forEach(function (ev) {
        window.addEventListener(ev, ensure, { capture: true, passive: true });
    });

    function out(volume) {
        var g = ctx.createGain();
        g.gain.value = volume;
        g.connect(ctx.destination);
        return g;
    }

    // One enveloped oscillator note
    function tone(dest, opts) {
        var o = ctx.createOscillator(), g = ctx.createGain();
        var t = opts.at, a = opts.attack || 0.008, d = opts.dur;
        o.type = opts.type || 'sine';
        o.frequency.setValueAtTime(opts.from, t);
        if (opts.to) o.frequency.exponentialRampToValueAtTime(opts.to, t + (opts.glide || d));
        g.gain.setValueAtTime(0.0001, t);
        g.gain.exponentialRampToValueAtTime(opts.gain || 1, t + a);
        g.gain.exponentialRampToValueAtTime(0.0001, t + d);
        o.connect(g); g.connect(dest);
        o.start(t); o.stop(t + d + 0.05);
    }

    // Short filtered noise burst (mechanical "click")
    function click(dest, at, gain, freq) {
        var len = Math.floor(ctx.sampleRate * 0.03);
        var buf = ctx.createBuffer(1, len, ctx.sampleRate), data = buf.getChannelData(0);
        for (var i = 0; i < len; i++) data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / len, 4);
        var src = ctx.createBufferSource(), f = ctx.createBiquadFilter(), g = ctx.createGain();
        f.type = 'bandpass'; f.frequency.value = freq || 3200; f.Q.value = 1.2;
        g.gain.value = gain;
        src.buffer = buf; src.connect(f); f.connect(g); g.connect(dest);
        src.start(at);
    }

    function bell(dest, freq, at, dur, gain) {
        tone(dest, { from: freq, at: at, dur: dur, gain: gain });
        tone(dest, { from: freq * 2.01, at: at, dur: dur * 0.6, gain: gain * 0.25 });
        tone(dest, { from: freq * 3, at: at, dur: dur * 0.35, gain: gain * 0.08, type: 'triangle' });
    }

    // Soft sustained chord under the bells: slow swell in, long fade out
    function pad(dest, freqs, at, dur, gain) {
        freqs.forEach(function (f) {
            tone(dest, { from: f, at: at, dur: dur, gain: gain, attack: 0.18 });
            tone(dest, { from: f * 1.003, at: at, dur: dur, gain: gain * 0.6, attack: 0.22, type: 'triangle' });
        });
    }

    // Each sound lasts about 1.5 seconds.
    var SOUNDS = {
        // Login: latch click, rising C-major arpeggio over a warm swelling chord, ringing out
        unlock: function (t) {
            var d = out(0.26);
            click(d, t, 0.9, 2600);
            pad(d, [261.63, 392], t + 0.05, 1.45, 0.12);
            bell(d, 523.25, t + 0.08, 0.9, 0.55);
            bell(d, 659.25, t + 0.22, 0.95, 0.6);
            bell(d, 783.99, t + 0.36, 1.0, 0.65);
            bell(d, 1046.5, t + 0.52, 0.98, 0.75);
        },
        // Logout: falling arpeggio over a fading chord, then the latch closing
        lock: function (t) {
            var d = out(0.24);
            pad(d, [392, 261.63], t, 1.45, 0.1);
            bell(d, 1046.5, t + 0.02, 0.8, 0.6);
            bell(d, 783.99, t + 0.18, 0.85, 0.6);
            bell(d, 659.25, t + 0.34, 0.9, 0.6);
            bell(d, 523.25, t + 0.5, 0.95, 0.65);
            click(d, t + 0.62, 0.85, 1600);
        },
        // New expense: bubbly pop, then a sparkling rise that rings out
        pop: function (t) {
            var d = out(0.28);
            tone(d, { from: 280, to: 1100, glide: 0.07, at: t, dur: 0.14, gain: 0.9 });
            click(d, t, 0.35, 5200);
            bell(d, 1318.51, t + 0.1, 0.7, 0.45);
            bell(d, 1567.98, t + 0.2, 0.8, 0.4);
            bell(d, 2093, t + 0.32, 1.15, 0.35);
        },
        // Payment recorded: bright "cha-ching" arpeggio with a long shimmer
        success: function (t) {
            var d = out(0.22);
            pad(d, [523.25, 783.99], t + 0.25, 1.25, 0.08);
            [[783.99, 0, 0.6], [1046.5, 0.09, 0.6], [1318.51, 0.18, 0.7], [1567.98, 0.27, 1.2]].forEach(function (n) {
                bell(d, n[0], t + n[1], n[2], 1);
            });
        }
    };

    window.SFX = {
        prime: ensure,
        play: function (name) {
            if (!ensure() || !SOUNDS[name]) return;
            try { SOUNDS[name](ctx.currentTime + 0.02); } catch (e) {}
        }
    };
})();
