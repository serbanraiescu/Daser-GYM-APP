<div id="license-grace-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:99998; display:flex; align-items:center; justify-content:center; font-family: sans-serif;">
    <div style="background:white; border-radius:12px; padding:30px; max-width:450px; width:90%; text-align:center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="font-size: 48px; color: #dc2626; margin-bottom: 20px;">🔔</div>
        <h2 style="font-size: 20px; font-weight: bold; color: #111827; margin-bottom: 15px;">Licența a Expirat!</h2>
        <p style="color: #4b5563; line-height: 1.5; margin-bottom: 25px;">
            Aveți <b>{{ $days }}</b> zile de grație rămase. După acest interval, accesul la aplicație va fi blocat complet.
        </p>
        <button onclick="document.getElementById('license-grace-modal').style.display='none'" style="background:#dc2626; color:white; padding:12px 24px; border:none; border-radius:8px; font-weight:bold; cursor:pointer; width:100%;">
            Continuă utilizarea ({{ $days }} zile rămase)
        </button>
    </div>
</div>
<div style="background: #fee2e2; border-bottom: 1px solid #fecaca; padding: 8px 20px; text-align: center; color: #991b1b; font-size: 13px; font-weight: bold;">
    PERIOADĂ DE GRAȚIE: {{ $days }} zile rămase
</div>
