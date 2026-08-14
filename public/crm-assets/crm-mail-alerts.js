(function () {
  var baseTitle = document.title.replace(/^\(\d+\)\s*/, '').replace(/^📧\s*New mail!\s*[—\-]\s*/i, '');
  var blinkTimer = null;
  var blinkOn = false;
  var audioCtx = null;

  function ensureAudio() {
    if (!audioCtx) {
      var Ctx = window.AudioContext || window.webkitAudioContext;
      if (Ctx) audioCtx = new Ctx();
    }
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume().catch(function () {});
    }
    return audioCtx;
  }

  function playTone() {
    try {
      var ctx = ensureAudio();
      if (!ctx) return;
      var now = ctx.currentTime;
      [880, 1175].forEach(function (freq, i) {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(0.18, now + 0.02 + i * 0.08);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.28 + i * 0.1);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now + i * 0.1);
        osc.stop(now + 0.35 + i * 0.1);
      });
    } catch (e) {}
  }

  function setTabBadge(count) {
    if (count > 0) {
      document.title = '(' + count + ') ' + baseTitle;
    } else {
      document.title = baseTitle;
      stopBlink();
    }
  }

  function startBlink(count, summary) {
    stopBlink();
    var alertTitle = '📧 New mail! — ' + (summary || (count + ' unread'));
    blinkTimer = setInterval(function () {
      blinkOn = !blinkOn;
      document.title = blinkOn ? alertTitle : '(' + count + ') ' + baseTitle;
    }, 1200);
    setTimeout(stopBlink, 20000);
  }

  function stopBlink() {
    if (blinkTimer) {
      clearInterval(blinkTimer);
      blinkTimer = null;
    }
    blinkOn = false;
  }

  function desktopNotify(summary, count) {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
      Notification.requestPermission().catch(function () {});
      return;
    }
    if (Notification.permission !== 'granted') return;
    try {
      var n = new Notification('CodoVision CRM — New mail', {
        body: summary || (count + ' unread email alert(s)'),
        tag: 'crm-mail-' + Date.now(),
        renotify: true
      });
      n.onclick = function () {
        window.focus();
        window.location.href = '/crm/mailbox';
        n.close();
      };
    } catch (e) {}
  }

  function showBanner(summary) {
    var existing = document.getElementById('crm-mail-live-banner');
    if (existing) existing.remove();
    var el = document.createElement('div');
    el.id = 'crm-mail-live-banner';
    el.className = 'crm-mail-live-banner';
    el.innerHTML = '<strong>New mail</strong><span>' + escapeHtml(summary || 'You have a new email') + '</span><a href="/crm/mailbox">Open mailbox</a><button type="button" aria-label="Dismiss">✕</button>';
    el.querySelector('button').onclick = function () { el.remove(); };
    document.body.appendChild(el);
    setTimeout(function () { if (el.parentNode) el.remove(); }, 12000);
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function onNewMail(detail) {
    var count = detail.count || 0;
    var summary = detail.summary || '';
    playTone();
    setTabBadge(count);
    startBlink(count, summary);
    desktopNotify(summary, count);
    showBanner(summary);
    updateNavBadge(count);
  }

  function updateNavBadge(count) {
    document.querySelectorAll('[data-crm-mail-nav-badge]').forEach(function (el) {
      if (count > 0) {
        el.hidden = false;
        el.textContent = String(count);
      } else {
        el.hidden = true;
      }
    });
  }

  // Unlock audio after first user gesture (browser requirement).
  ['click', 'keydown', 'touchstart'].forEach(function (evt) {
    document.addEventListener(evt, function () { ensureAudio(); }, { once: true, passive: true });
  });

  document.addEventListener('livewire:init', function () {
    Livewire.on('crm-new-mail', function (payload) {
      var detail = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
      onNewMail(detail);
    });
    Livewire.on('crm-mail-unread', function (payload) {
      var detail = Array.isArray(payload) ? (payload[0] || {}) : (payload || {});
      setTabBadge(detail.count || 0);
      updateNavBadge(detail.count || 0);
    });
  });

  if ('Notification' in window && Notification.permission === 'default') {
    // Soft prompt after load.
    setTimeout(function () {
      Notification.requestPermission().catch(function () {});
    }, 2500);
  }
})();
