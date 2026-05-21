<style>
/* ── Shared index-page styles ── */
.idx-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600; padding: 3px 9px; border-radius: 20px;
    white-space: nowrap;
}
.chip-green  { background: #dcfce7; color: #15803d; }
.chip-blue   { background: #dbeafe; color: #1d4ed8; }
.chip-yellow { background: #fef3c7; color: #b45309; }
.chip-orange { background: #ffedd5; color: #c2410c; }
.chip-gray   { background: #f3f4f6; color: #6b7280; }
.chip-red    { background: #fee2e2; color: #dc2626; }
.chip-violet { background: #ede9fe; color: #5b21b6; }
.chip-teal   { background: #ccfbf1; color: #0d9488; }
.chip-pink   { background: #fce7f3; color: #db2777; }
.chip-cyan   { background: #e0f2fe; color: #0284c7; }

.idx-btn {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .75rem; font-weight: 600; padding: 5px 11px;
    border-radius: 7px; border: none; cursor: pointer; text-decoration: none;
    transition: opacity .15s;
}
.idx-btn:hover  { opacity: .82; text-decoration: none; }
.ib-view        { background: #dbeafe; color: #1d4ed8; }
.ib-edit        { background: #fef3c7; color: #b45309; }
.ib-del         { background: #fee2e2; color: #dc2626; }
.ib-green       { background: #dcfce7; color: #15803d; }
.ib-purple      { background: #ede9fe; color: #5b21b6; }
.ib-teal        { background: #ccfbf1; color: #0d9488; }
.ib-gray        { background: #f3f4f6; color: #374151; }

.idx-table th {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: #6b7280; background: #f9fafb !important;
    border-bottom: 1px solid #e5e7eb !important; white-space: nowrap;
    padding: 10px 14px !important;
}
.idx-table td {
    font-size: .83rem; color: #374151; vertical-align: middle !important;
    padding: 10px 14px !important; border-color: #f3f4f6 !important;
}
.idx-table tbody tr:hover { background: #fafafa; }

.idx-card { border-radius: 14px !important; border: 1px solid #e5e7eb !important; box-shadow: 0 1px 4px rgba(0,0,0,.05) !important; }
.idx-card .card-header {
    background: #fff !important; border-bottom: 1px solid #e5e7eb !important;
    border-radius: 14px 14px 0 0 !important; padding: 16px 20px !important;
    display: flex !important; align-items: center; justify-content: space-between;
}
.idx-card .card-header h3 { font-size: 1rem; font-weight: 700; color: #111827; margin: 0; }
.idx-card .card-body { padding: 0 !important; }

.idx-flash {
    margin: 14px 20px 0; padding: 10px 14px; border-radius: 8px; font-size: .83rem;
    display: flex; align-items: center; gap: 8px;
}
.idx-flash-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.idx-flash-error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

.idx-empty {
    text-align: center; padding: 36px 16px !important;
    color: #9ca3af; font-size: .85rem; line-height: 2;
}
</style>
