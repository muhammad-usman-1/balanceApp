<style>
.content-wrapper { background: #fff !important; }
.mf-label { font-size: .75rem; font-weight: 700; color: #374151; text-transform: uppercase;
            letter-spacing: .04em; display: block; margin-bottom: 6px; }
.mf-input { width: 100%; padding: 9px 12px; font-size: .85rem; color: #111827;
            border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; outline: none;
            transition: border-color .15s, box-shadow .15s; box-sizing: border-box; }
.mf-input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.mf-input.is-err { border-color: #ef4444; }
.mf-err { font-size: .75rem; color: #ef4444; margin-top: 4px; }
.mf-section { font-size: .7rem; font-weight: 800; color: #9ca3af; text-transform: uppercase;
              letter-spacing: .08em; margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
.mf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
.mf-field { margin-bottom: 0; }
@media(max-width:640px){ .mf-grid-2 { grid-template-columns: 1fr; } }

/* Toggle switch */
.toggle-wrap { display: flex; align-items: center; gap: 10px; }
.toggle { position: relative; width: 42px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: #e5e7eb; border-radius: 24px;
                 cursor: pointer; transition: background .2s; }
.toggle-slider:before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px;
                        background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.toggle input:checked + .toggle-slider { background: #22c55e; }
.toggle input:checked + .toggle-slider:before { transform: translateX(18px); }
.toggle-label { font-size: .84rem; color: #374151; font-weight: 500; }
</style>
