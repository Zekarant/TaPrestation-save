@extends('layouts.app')
@section('title', 'Commandes Cuisine')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* =========================================================
   FOOD ORDERS DASHBOARD — TAPRESTATION
   Responsive : Kanban Desktop + Swipe Mobile
   ========================================================= */

:root {
    --orange: #f97316; --orange-d: #ea580c;
    --green: #22c55e;  --green-d: #16a34a;
    --blue: #3b82f6;   --blue-d: #2563eb;
    --purple: #a855f7;
    --red: #ef4444;
    --amber: #f59e0b;
    --dark: #0f172a;   --dark2: #1e293b;
    --gray: #334155;   --gray2: #475569;
    --text: #e2e8f0;   --muted: #94a3b8;
    --radius: 12px;
}

/* ── RESET ── */
.fd * { box-sizing: border-box; margin: 0; padding: 0; }
.fd { background: var(--dark); color: var(--text); font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; min-height: 100vh; }

/* ── HEADER ── */
.fd-header {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark2) 100%);
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    position: sticky; top: var(--site-nav-h, 70px); z-index: 100;
    border-bottom: 1px solid var(--gray);
    box-shadow: 0 4px 20px rgba(0,0,0,.25);
}
.fd-header-left { display: flex; align-items: center; gap: 14px; }
.fd-logo {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--orange), var(--orange-d));
    border-radius: var(--radius); display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.35rem;
    box-shadow: 0 4px 14px rgba(249,115,22,.35);
}
.fd-header-title { font-size: 1.15rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
.fd-header-meta { display: flex; align-items: center; gap: 10px; margin-top: 3px; }
.fd-live { display: flex; align-items: center; gap: 5px; font-size: .7rem; color: var(--muted); }
.fd-live .dot { width: 8px; height: 8px; background: var(--green); border-radius: 50%; animation: fd-pulse 2s infinite; }
@keyframes fd-pulse {
    0%,100% { opacity:1; box-shadow: 0 0 0 0 rgba(34,197,94,.4); }
    50% { opacity:.7; box-shadow: 0 0 0 6px rgba(34,197,94,0); }
}
.fd-clock { font-family: 'Courier New', monospace; font-size: .78rem; color: #fff; background: rgba(255,255,255,.08); padding: 3px 10px; border-radius: 6px; font-weight: 600; }

/* Header Buttons */
.fd-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.fd-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 14px; border-radius: 10px;
    font-size: .8rem; font-weight: 600; border: none; cursor: pointer;
    text-decoration: none; color: #fff !important; transition: all .2s;
}
.fd-btn:link,
.fd-btn:visited,
.fd-btn:hover,
.fd-btn:active {
    color: #fff !important;
    text-decoration: none;
}
.fd-btn i,
.fd-btn span {
    color: inherit !important;
}
.fd-btn i { font-size: .9rem; }
.fd-btn:hover { transform: translateY(-1px); }
.fd-btn.sound { background: rgba(255,255,255,.1); padding: 9px 11px; }
.fd-btn.sound:hover { background: rgba(255,255,255,.2); }
.fd-txt-mobile { display: none; }
.fd-m-only { display: none; }
.fd-d-only { display: inline; }
.fd-btn.sound.muted,
.fd-btn.sound.muted:link,
.fd-btn.sound.muted:visited,
.fd-btn.sound.muted i { color: #64748b !important; }
.fd-btn.open { border: 1px solid rgba(255,255,255,.18); }
.fd-btn.open .fd-open-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,.95);
    flex-shrink: 0;
}
.fd-btn.open.is-open { background: linear-gradient(135deg, var(--green), var(--green-d)); }
.fd-btn.open.is-open:hover { box-shadow: 0 4px 14px rgba(34,197,94,.4); }
.fd-btn.open.is-closed { background: linear-gradient(135deg, var(--red), #dc2626); }
.fd-btn.open.is-closed:hover { box-shadow: 0 4px 14px rgba(239,68,68,.4); }
.fd-btn.refresh { background: linear-gradient(135deg, var(--orange), var(--orange-d)); }
.fd-btn.refresh:hover { box-shadow: 0 4px 14px rgba(249,115,22,.4); }
.fd-btn.ldv { background: linear-gradient(135deg, var(--green), var(--green-d)); }
.fd-btn.ldv:hover { box-shadow: 0 4px 14px rgba(34,197,94,.4); }
.fd-btn.mnu { background: linear-gradient(135deg, var(--blue), var(--blue-d)); }
.fd-btn.mnu:hover { box-shadow: 0 4px 14px rgba(59,130,246,.4); }
.fd-btn.hm { background: rgba(255,255,255,.12); padding: 9px 11px; }
.fd-btn.hm:hover { background: rgba(255,255,255,.22); }

@media (max-width: 640px) {
    .fd-header { padding: 10px 12px; }
    .fd-btn { padding: 9px 11px; }
    .fd-actions .fd-btn i { display: none !important; }
    .fd-actions .fd-btn span { display: inline !important; }
    .fd-actions .fd-btn .fd-txt-desktop { display: none !important; }
    .fd-actions .fd-btn .fd-txt-mobile { display: inline !important; }
    .fd-actions .fd-btn {
        font-size: .66rem;
        line-height: 1.1;
        gap: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fd-clock { display: none; }
    .fd-logo { width: 40px; height: 40px; font-size: 1.1rem; }
    .fd-header-title { font-size: 1rem; }
    .fd-m-only { display: inline; }
    .fd-d-only { display: none; }
}

/* ── STATS BAR ── */
.fd-stats {
    display: flex; gap: 0; border-bottom: 1px solid var(--gray);
    background: var(--dark2); overflow-x: auto; scrollbar-width: none;
}
.fd-stats::-webkit-scrollbar { display: none; }
.fd-stat {
    flex: 1; min-width: 120px;
    padding: 12px 14px; text-align: center;
    border-right: 1px solid var(--gray);
    display: flex; flex-direction: column; gap: 2px;
    cursor: pointer;
    transition: background .15s, transform .15s;
}
.fd-stat:hover { background: rgba(255,255,255,.03); }
.fd-stat:focus-visible { outline: 2px solid rgba(249,115,22,.7); outline-offset: -2px; }
.fd-stat:last-child { border-right: none; }
.fd-stat-val { font-size: 1.1rem; font-weight: 800; }
.fd-stat-val.green { color: var(--green); }
.fd-stat-val.orange { color: var(--orange); }
.fd-stat-val.blue { color: var(--blue); }
.fd-stat-val.purple { color: var(--purple); }
.fd-stat-lbl { font-size: .62rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }

@media (max-width: 640px) {
    .fd-stat { min-width: 95px; padding: 10px 10px; }
    .fd-stat-val { font-size: .92rem; }
}

/* ── MAIN TABS ── */
.fd-tabs {
    display: flex; background: var(--dark2);
    border-bottom: 1px solid var(--gray);
}
.fd-tab {
    flex: 1; padding: 12px 8px; text-align: center; cursor: pointer;
    border-bottom: 3px solid transparent; transition: all .2s;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    position: relative;
}
.fd-tab:hover { background: rgba(255,255,255,.03); }
.fd-tab.active { border-color: var(--orange); background: rgba(249,115,22,.08); }
.fd-tab-icon { font-size: 1.15rem; color: var(--muted); }
.fd-tab.active .fd-tab-icon { color: var(--orange); }
.fd-tab-label { font-size: .68rem; font-weight: 600; color: var(--muted); }
.fd-tab.active .fd-tab-label { color: var(--orange); }
.fd-tab-badge {
    position: absolute; top: 6px; right: calc(50% - 24px);
    background: var(--red); color: #fff;
    font-size: .6rem; font-weight: 700;
    padding: 2px 6px; border-radius: 99px; min-width: 18px;
    animation: fd-badge-pop .3s ease;
}
@keyframes fd-badge-pop { from { transform: scale(0); } to { transform: scale(1); } }

@media (min-width: 768px) {
    .fd-tab { flex-direction: row; gap: 8px; padding: 14px 20px; }
    .fd-tab-icon { font-size: 1.2rem; }
    .fd-tab-label { font-size: .85rem; }
    .fd-tab-badge { position: static; }
}

/* ── KITCHEN SUB-TABS (Mobile) ── */
.fd-subtabs {
    display: flex; gap: 6px; padding: 10px 12px;
    background: var(--dark2); border-bottom: 1px solid var(--gray);
    overflow-x: auto; scrollbar-width: none;
}
.fd-subtabs::-webkit-scrollbar { display: none; }
.fd-subtab {
    flex: 1; min-width: 0; padding: 10px 12px; border-radius: 10px;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    white-space: nowrap; border: 1px solid var(--gray);
    background: var(--dark); color: var(--muted); transition: all .2s;
}
.fd-subtab i { font-size: .85rem; }
.fd-subtab:hover { border-color: var(--text); color: var(--text); }
.fd-subtab.active { color: #fff; }
.fd-subtab .cnt { background: rgba(255,255,255,.25); padding: 2px 7px; border-radius: 99px; font-size: .68rem; font-weight: 700; }
.fd-subtab.active .cnt { background: rgba(0,0,0,.2); }
.fd-subtab.pending.active  { background: var(--amber); border-color: var(--amber); }
.fd-subtab.accepted.active { background: var(--blue); border-color: var(--blue); }
.fd-subtab.preparing.active{ background: var(--purple); border-color: var(--purple); }
.fd-subtab.ready.active    { background: var(--green); border-color: var(--green); }

/* ── SWIPE (Mobile) ── */
.fd-swipe { position: relative; overflow: hidden; touch-action: pan-y; }
.fd-swipe-wrap { display: flex; transition: transform .3s ease; }
.fd-swipe-panel { flex: 0 0 100%; min-height: calc(100vh - 300px); padding: 10px; overflow-y: auto; }

/* ── ORDER CARDS ── */
.fd-order-list { display: flex; flex-direction: column; gap: 8px; }
.fd-card {
    background: var(--dark2); border-radius: var(--radius); padding: 14px;
    border: 1px solid var(--gray); cursor: pointer;
    transition: all .15s; position: relative; overflow: hidden;
}
.fd-card:hover { border-color: var(--orange); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.2); }
.fd-card.urgent { border-left: 4px solid var(--red); }
.fd-card.urgent::before {
    content: '⚡ Urgent'; position: absolute; top: 8px; right: 10px;
    font-size: .65rem; background: var(--red); color: #fff;
    padding: 2px 8px; border-radius: 99px; font-weight: 600;
}
.fd-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.fd-card-num { font-weight: 800; font-size: 1rem; color: #fff; }
.fd-card-time { font-size: .72rem; color: var(--muted); background: rgba(255,255,255,.06); padding: 3px 8px; border-radius: 6px; }
.fd-card-time.scheduled { background: var(--blue); color: #fff; font-weight: 700; }
.fd-card-client { font-size: .85rem; color: var(--orange); font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
.fd-card-items { margin-bottom: 8px; }
.fd-card-item { font-size: .82rem; color: var(--muted); padding: 2px 0; display: flex; align-items: center; gap: 6px; }
.fd-card-item .qty { background: rgba(255,255,255,.08); color: #fff; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: .72rem; min-width: 26px; text-align: center; }
.fd-card-more { font-size: .75rem; color: var(--gray2); font-style: italic; margin-top: 2px; }
.fd-card-foot { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.fd-card-price { font-weight: 800; font-size: 1.05rem; color: #fff; }
.fd-card-type { font-size: .68rem; font-weight: 700; text-transform: uppercase; padding: 3px 10px; border-radius: 99px; letter-spacing: .3px; }
.fd-card-type.delivery { background: rgba(59,130,246,.15); color: #93c5fd; }
.fd-card-type.pickup { background: rgba(34,197,94,.15); color: #86efac; }

/* Payment Badge */
.fd-pay { font-size: .65rem; font-weight: 600; padding: 2px 8px; border-radius: 99px; margin-left: 6px; }
.fd-pay.cash { background: rgba(245,158,11,.15); color: #fcd34d; }
.fd-pay.paid { background: rgba(34,197,94,.15); color: #86efac; }
.fd-pay.pending { background: rgba(239,68,68,.15); color: #fca5a5; }

/* ── Payment Info Row ── */
.fd-payment-info { margin-bottom: 8px; }
.fd-pay-row {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 10px; border-radius: 8px; font-size: .78rem;
}
.fd-pay-row.cash-row    { background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.2); }
.fd-pay-row.deposit-row { background: rgba(99,102,241,.08); border: 1px solid rgba(99,102,241,.2); }
.fd-pay-row.online-row  { background: rgba(59,130,246,.08); border: 1px solid rgba(59,130,246,.2); }
.fd-pay-icon { font-size: 1.1rem; flex-shrink: 0; }
.fd-pay-text { flex: 1; color: var(--text); font-weight: 600; line-height: 1.3; }
.fd-pay-text small { display: block; font-weight: 400; font-size: .7rem; color: var(--muted); margin-top: 1px; }
.fd-pay-tag { font-size: .62rem; font-weight: 700; padding: 3px 8px; border-radius: 99px; white-space: nowrap; flex-shrink: 0; }
.fd-pay-tag.paid    { background: rgba(34,197,94,.2); color: #86efac; }
.fd-pay-tag.capture { background: rgba(99,102,241,.2); color: #a5b4fc; }
.fd-pay-tag.pending { background: rgba(239,68,68,.15); color: #fca5a5; }

/* ── Context Info (driver status, warnings) ── */
.fd-ctx-info { margin-bottom: 8px; }
.fd-ctx {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 10px; border-radius: 8px; font-size: .75rem; font-weight: 600;
}
.fd-ctx.ok   { background: rgba(34,197,94,.1); color: #86efac; }
.fd-ctx.warn { background: rgba(239,68,68,.08); color: #fca5a5; border: 1px solid rgba(239,68,68,.15); }
.fd-ctx.info { background: rgba(59,130,246,.08); color: #93c5fd; }
.fd-ctx i { font-size: .8rem; }

/* Special Notes */
.fd-card-notes { background: rgba(249,115,22,.08); border-left: 3px solid var(--orange); padding: 6px 10px; margin-bottom: 10px; border-radius: 0 6px 6px 0; font-size: .78rem; color: var(--amber); }

/* Card Buttons */
.fd-card-btns { display: flex; gap: 6px; }
.fd-card-btns form { flex: 1; }
.fd-cbtn {
    width: 100%; border: none; padding: 10px 8px; border-radius: 8px;
    font-weight: 700; font-size: .85rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: all .15s; color: #fff;
}
.fd-cbtn:active { transform: scale(.97); }
.fd-cbtn.accept  { background: linear-gradient(135deg, var(--green), var(--green-d)); }
.fd-cbtn.reject  { background: rgba(239,68,68,.15); color: var(--red); flex: 0 0 44px; }
.fd-cbtn.reject:hover { background: rgba(239,68,68,.3); }
.fd-cbtn.cook    { background: linear-gradient(135deg, var(--purple), #9333ea); }
.fd-cbtn.ready   { background: linear-gradient(135deg, var(--green), var(--green-d)); }
.fd-cbtn.deliver { background: linear-gradient(135deg, var(--blue), var(--blue-d)); }
.fd-cbtn.code    { background: linear-gradient(135deg, var(--orange), var(--orange-d)); }
.fd-cbtn.disabled { background: var(--gray); color: var(--muted); cursor: not-allowed; opacity: .7; }
.fd-cbtn.cash-confirm { background: linear-gradient(135deg, var(--amber), #d97706); }
.fd-cbtn.deliver-self { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.fd-cbtn.convert-pickup { background: rgba(245,158,11,.15); color: var(--amber); flex: 0 0 auto; min-width: 90px; }
.fd-cbtn.convert-pickup:hover { background: rgba(245,158,11,.3); }

/* ── EMPTY STATE ── */
.fd-empty { text-align: center; padding: 50px 20px; color: var(--muted); }
.fd-empty-ico { font-size: 2.8rem; margin-bottom: 10px; opacity: .4; }
.fd-empty p { font-size: .85rem; }
.fd-empty-sub { font-size: .75rem; margin-top: 5px; color: var(--gray2); }

/* ── TAB CONTENT ── */
.fd-content { display: none; }
.fd-content.active { display: block; }

/* ── DELIVERY TAB ── */
.fd-del-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 12px; padding: 12px; }
.fd-del-card { background: var(--dark2); border-radius: var(--radius); overflow: hidden; border: 1px solid var(--gray); }
.fd-del-head { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; }
.fd-del-head .num { font-weight: 800; font-size: .95rem; }
.fd-del-badge { background: rgba(255,255,255,.2); padding: 4px 12px; border-radius: 99px; font-size: .68rem; font-weight: 600; }
.fd-del-body { padding: 14px; display: flex; flex-direction: column; gap: 10px; }
.fd-del-addr { background: var(--gray); padding: 10px 12px; border-radius: 8px; font-size: .82rem; display: flex; align-items: flex-start; gap: 8px; }
.fd-del-client { font-size: .82rem; color: var(--muted); display: flex; align-items: center; gap: 8px; }
.fd-del-driver { display: flex; align-items: center; gap: 12px; padding: 10px 12px; background: #064e3b; border-radius: 8px; }
.fd-del-driver .avatar { width: 36px; height: 36px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.fd-del-driver .info { font-size: .82rem; color: #d1fae5; }
.fd-del-driver .info strong { display: block; }
.fd-del-waiting { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #78350f; border-radius: 8px; color: #fef3c7; font-size: .82rem; }
.fd-del-waiting i { animation: fd-pulse 1.5s infinite; }
.fd-del-pay { background: rgba(255,255,255,.05); padding: 8px 12px; border-radius: 8px; font-size: .82rem; color: var(--muted); border: 1px solid var(--gray); }
.fd-del-actions { display: flex; gap: 8px; }
.fd-del-actions .fd-cbtn { flex: 1; }
.fd-del-assign-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 8px;
    margin-top: 8px;
    align-items: stretch;
}
.fd-del-assign-select {
    width: 100%;
    min-width: 0;
    background: #0f172a;
    color: #e2e8f0;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 8px 10px;
    min-height: 44px;
}
.fd-del-assign-form .fd-cbtn {
    width: auto;
    min-width: 116px;
    padding-left: 14px;
    padding-right: 14px;
    white-space: nowrap;
}

/* ── INTERNAL ROUTE BOARD ── */
.fd-int-board { padding: 12px; display: flex; flex-direction: column; gap: 12px; }
.fd-int-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; }
.fd-int-stat {
    background: var(--dark2); border: 1px solid var(--gray); border-radius: 10px;
    padding: 10px 12px;
    width: 100%;
    text-align: left;
    color: inherit;
    font: inherit;
    cursor: pointer;
    transition: border-color .15s, background .15s, transform .15s, box-shadow .15s;
}
.fd-int-stat:hover { border-color: rgba(249,115,22,.7); transform: translateY(-1px); }
.fd-int-stat.active {
    border-color: rgba(249,115,22,.85);
    background: rgba(249,115,22,.08);
    box-shadow: inset 0 0 0 1px rgba(249,115,22,.18);
}
.fd-int-stat:focus-visible { outline: 2px solid rgba(249,115,22,.75); outline-offset: 2px; }
.fd-int-stat .lbl { font-size: .68rem; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
.fd-int-stat .val { font-size: 1.1rem; font-weight: 800; color: #fff; margin-top: 3px; }
.fd-int-filterbar {
    display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
    padding: 10px 12px; border: 1px solid var(--gray); border-radius: 10px;
    background: rgba(255,255,255,.03);
}
.fd-int-filtertext { font-size: .76rem; color: var(--muted); font-weight: 600; }
.fd-int-filterbtn {
    border: 1px solid var(--gray); background: var(--dark); color: #fff;
    border-radius: 999px; padding: 7px 12px; font-size: .72rem; font-weight: 700; cursor: pointer;
}
.fd-int-filterbtn:hover { border-color: rgba(249,115,22,.75); color: #fed7aa; }
.fd-int-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px; }
.fd-int-card.is-hidden { display: none; }
.fd-int-card { background: var(--dark2); border: 1px solid var(--gray); border-radius: 12px; overflow: hidden; }
.fd-int-head {
    padding: 10px 12px;
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    background: rgba(59,130,246,.12);
}
.fd-int-driver { font-size: .9rem; font-weight: 700; color: #fff; }
.fd-int-status {
    font-size: .68rem; font-weight: 700; padding: 3px 8px; border-radius: 99px;
    background: rgba(34,197,94,.16); color: #86efac;
}
.fd-int-status.busy { background: rgba(245,158,11,.16); color: #fcd34d; }
.fd-int-body { padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.fd-int-meta { display: flex; flex-wrap: wrap; gap: 6px; }
.fd-int-pill {
    font-size: .72rem; font-weight: 700; padding: 3px 8px; border-radius: 99px;
    background: rgba(255,255,255,.06); color: var(--text); border: 1px solid var(--gray);
}
.fd-int-route { display: flex; flex-direction: column; gap: 6px; margin-top: 2px; }
.fd-int-point {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: .78rem; color: var(--muted); background: rgba(255,255,255,.03);
    border: 1px solid var(--gray); border-radius: 8px; padding: 7px 8px;
}
.fd-int-n {
    width: 18px; height: 18px; border-radius: 99px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .65rem; font-weight: 700; color: #fff; background: var(--blue);
    flex-shrink: 0; margin-top: 1px;
}
.fd-int-point.pickup .fd-int-n { background: var(--amber); color: #111827; }
.fd-int-point strong { color: #fff; }
.fd-int-empty {
    padding: 9px; border: 1px dashed var(--gray); border-radius: 8px;
    font-size: .78rem; color: var(--muted);
}
.fd-int-phone {
    margin-top: 2px;
    font-size: .76rem;
    color: #93c5fd;
    text-decoration: none;
}
.fd-int-phone:hover { color: #bfdbfe; }

/* ── HISTORY TAB ── */
.fd-history { padding: 12px; }
.fd-hist-filters { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.fd-hist-filters select, .fd-hist-filters input {
    background: var(--dark2); border: 1px solid var(--gray); color: #fff;
    padding: 10px 14px; border-radius: 8px; font-size: .85rem; min-width: 140px;
}
.fd-hist-filters select:focus, .fd-hist-filters input:focus { border-color: var(--orange); outline: none; }
.fd-hist-field { display: flex; flex-direction: column; gap: 4px; min-width: 160px; }
.fd-hist-field span { font-size: .64rem; color: var(--muted); font-weight: 700; letter-spacing: .4px; text-transform: uppercase; }
.fd-hist-actions { display: flex; align-items: flex-end; }
.fd-hist-apply {
    border: 1px solid rgba(249,115,22,.65); background: rgba(249,115,22,.12); color: #fdba74;
    border-radius: 8px; padding: 10px 14px; font-size: .8rem; font-weight: 700; cursor: pointer;
}
.fd-hist-apply:hover { background: rgba(249,115,22,.18); }
.fd-hist-filters input[type="date"] {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    display: block;
    box-sizing: border-box;
    line-height: 1.2;
    font-variant-numeric: tabular-nums;
}
.fd-hist-filters input[type="date"]::-webkit-date-and-time-value {
    text-align: left;
}
.fd-del-grid.fd-focus {
    border-radius: 12px;
    box-shadow: 0 0 0 1px rgba(249,115,22,.75), 0 0 0 4px rgba(249,115,22,.12);
}
/* iOS Safari: avoid intrinsic overflow and zoom quirks on date inputs */
@supports (-webkit-touch-callout: none) {
    .fd-hist-filters input[type="date"] {
        font-size: 16px;
        padding-right: 10px;
    }
}
.fd-hist-list { display: flex; flex-direction: column; gap: 6px; }
.fd-hist-item {
    background: var(--dark2); border-radius: 10px; padding: 14px 16px;
    display: flex; align-items: center; gap: 14px;
    cursor: pointer; border: 1px solid var(--gray); transition: all .15s;
}
.fd-hist-item:hover { border-color: var(--orange); }
.fd-hist-num { font-weight: 800; font-size: .9rem; min-width: 80px; color: #fff; }
.fd-hist-info { flex: 1; font-size: .82rem; color: var(--muted); }
.fd-hist-total { font-weight: 800; color: var(--green); font-size: .95rem; min-width: 70px; text-align: right; }
.fd-status { padding: 4px 12px; border-radius: 99px; font-size: .68rem; font-weight: 600; }
.fd-status.delivered, .fd-status.completed { background: rgba(34,197,94,.15); color: #86efac; }
.fd-status.cancelled { background: rgba(239,68,68,.15); color: #fca5a5; }

/* ── AGENDA TAB ── */
.fd-agenda { padding: 12px; }
.fd-agenda-list { display: flex; flex-direction: column; gap: 8px; }
.fd-agenda-day { font-size: .78rem; font-weight: 700; color: var(--orange); text-transform: uppercase; padding: 8px 0; border-bottom: 1px solid var(--gray); margin-top: 8px; }
.fd-agenda-card {
    background: var(--dark2); border-radius: var(--radius); padding: 14px;
    border: 1px solid var(--gray); border-left: 4px solid var(--blue);
    display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all .15s;
}
.fd-agenda-card:hover { border-color: var(--orange); }
.fd-agenda-time { font-size: .85rem; font-weight: 700; color: var(--blue); min-width: 70px; text-align: center; }
.fd-agenda-detail { flex: 1; }
.fd-agenda-client { font-size: .85rem; font-weight: 600; color: #fff; }
.fd-agenda-items { font-size: .78rem; color: var(--muted); margin-top: 2px; }
.fd-agenda-total { font-weight: 800; font-size: .95rem; color: #fff; }

/* ── CODE MODAL ── */
.fd-modal {
    position: fixed; inset: 0; background: rgba(0,0,0,.85);
    display: flex; align-items: center; justify-content: center;
    z-index: 200; opacity: 0; visibility: hidden; transition: all .2s;
}
.fd-modal.active { opacity: 1; visibility: visible; }
.fd-modal-box {
    background: var(--dark2); border-radius: 20px; padding: 28px;
    width: 90%; max-width: 380px; text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.5); border: 1px solid var(--gray);
}
.fd-modal-box h3 { font-size: 1.15rem; margin-bottom: 6px; color: #fff; }
.fd-modal-sub { font-size: .82rem; color: var(--muted); margin-bottom: 24px; }
.fd-code-inputs { display: flex; justify-content: center; gap: 12px; margin-bottom: 20px; }
.fd-code-inputs input {
    width: 56px; height: 64px;
    background: var(--dark); border: 2px solid var(--gray);
    border-radius: var(--radius); text-align: center;
    font-size: 1.8rem; font-weight: 800; color: #fff; transition: border-color .2s;
}
.fd-code-inputs input:focus { border-color: var(--orange); outline: none; }
.fd-code-error { color: var(--red); font-size: .82rem; margin-bottom: 16px; display: none; }
.fd-modal-btns { display: flex; gap: 12px; }
.fd-modal-btns button { flex: 1; padding: 14px; border-radius: 10px; border: none; font-weight: 700; font-size: .9rem; cursor: pointer; transition: all .15s; }
.fd-modal-btns .cancel { background: var(--gray); color: #fff; }
.fd-modal-btns .cancel:hover { background: var(--gray2); }
.fd-modal-btns .confirm { background: linear-gradient(135deg, var(--green), var(--green-d)); color: #fff; }
.fd-modal-btns .confirm:hover { box-shadow: 0 4px 14px rgba(34,197,94,.4); }
.fd-stat-modal .fd-modal-box {
    max-width: 760px;
    text-align: left;
    padding: 24px;
}
.fd-stat-modal-head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    margin-bottom: 14px;
}
.fd-stat-modal-meta { display: flex; flex-wrap: wrap; gap: 8px; }
.fd-stat-chip {
    font-size: .72rem; font-weight: 700; padding: 5px 10px; border-radius: 999px;
    border: 1px solid var(--gray); background: rgba(255,255,255,.04); color: #fff;
}
.fd-stat-modal-close {
    border: 1px solid var(--gray); background: rgba(255,255,255,.04); color: #fff;
    width: 38px; height: 38px; border-radius: 10px; cursor: pointer; flex-shrink: 0;
}
.fd-stat-modal-close:hover { border-color: rgba(249,115,22,.75); color: #fed7aa; }
.fd-stat-detail-list {
    display: flex; flex-direction: column; gap: 8px;
    max-height: min(60vh, 520px);
    overflow-y: auto;
    padding-right: 4px;
}
.fd-stat-detail-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 14px; border: 1px solid var(--gray); border-radius: 12px;
    background: rgba(255,255,255,.03); color: inherit; text-decoration: none;
    transition: border-color .15s, transform .15s;
}
.fd-stat-detail-item:hover { border-color: rgba(249,115,22,.75); transform: translateY(-1px); }
.fd-stat-detail-number { min-width: 96px; font-size: .88rem; font-weight: 800; color: #fff; }
.fd-stat-detail-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.fd-stat-detail-client { font-size: .84rem; font-weight: 700; color: #fff; }
.fd-stat-detail-meta { font-size: .76rem; color: var(--muted); margin-top: 2px; }
.fd-stat-detail-total { font-size: .92rem; font-weight: 800; color: var(--green); white-space: nowrap; }
.fd-stat-empty {
    padding: 18px; border: 1px dashed var(--gray); border-radius: 12px;
    text-align: center; color: var(--muted); font-size: .85rem;
}
.fd-stat-note {
    margin-top: 10px;
    font-size: .72rem;
    color: var(--muted);
}

/* ── DESKTOP KANBAN ── */
@media (min-width: 900px) {
    .fd-subtabs { display: none; }
    .fd-swipe { display: none; }
    .fd-kanban { display: grid !important; }
}
@media (max-width: 899px) {
    .fd-kanban { display: none !important; }
}
.fd-kanban {
    display: none;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px; padding: 10px;
    height: calc(100vh - 240px);
}
.fd-col {
    background: var(--dark2); border-radius: var(--radius);
    display: flex; flex-direction: column; overflow: hidden;
    border: 1px solid var(--gray);
}
.fd-col-head {
    padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;
    font-size: .82rem; font-weight: 700; border-bottom: 3px solid;
}
.fd-col-head.pending  { border-color: var(--amber); background: rgba(245,158,11,.1); }
.fd-col-head.accepted { border-color: var(--blue);  background: rgba(59,130,246,.1); }
.fd-col-head.preparing{ border-color: var(--purple); background: rgba(168,85,247,.1); }
.fd-col-head.ready    { border-color: var(--green);  background: rgba(34,197,94,.1); }
.fd-col-head .cnt { background: #fff; color: var(--dark); padding: 3px 10px; border-radius: 99px; font-size: .72rem; font-weight: 800; }
.fd-col-body { flex: 1; overflow-y: auto; padding: 6px; }

@media (min-width: 900px) {
    .fd-kanban .fd-card { padding: 10px 12px; }
    .fd-kanban .fd-card-head { margin-bottom: 4px; }
    .fd-kanban .fd-card-items { margin-bottom: 4px; }
    .fd-kanban .fd-card-foot { margin-bottom: 6px; }
    .fd-kanban .fd-cbtn { padding: 8px 6px; font-size: .8rem; }
}

/* Scrollbar */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--gray); border-radius: 3px; }

/* ── MOBILE FIXES (anti-chevauchement) ── */
@media (max-width: 640px) {
    .fd {
        min-height: auto;
        padding-bottom: calc(88px + env(safe-area-inset-bottom, 0px));
    }

    .fd-header {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
        padding: 8px 10px;
    }

    .fd-header-left {
        width: 100%;
        gap: 10px;
        align-items: center;
    }

    .fd-logo {
        width: 36px;
        height: 36px;
        font-size: 1rem;
        border-radius: 10px;
    }

    .fd-header-title {
        font-size: .92rem;
    }

    .fd-header-meta {
        gap: 6px;
        margin-top: 2px;
    }

    .fd-live {
        font-size: .62rem;
    }

    .fd-actions {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 5px;
    }

    .fd-actions .fd-btn i {
        display: block !important;
        font-size: .8rem;
    }

    .fd-actions .fd-btn span {
        display: block !important;
    }

    .fd-actions .fd-btn .fd-txt-desktop {
        display: none !important;
    }

    .fd-actions .fd-btn .fd-txt-mobile {
        display: inline !important;
    }

    .fd-btn {
        justify-content: center;
        flex-direction: column;
        min-height: 42px;
        padding: 6px 4px;
        gap: 2px;
        border-radius: 9px;
        font-size: .58rem;
        line-height: 1.05;
        white-space: normal;
        text-align: center;
    }

    .fd-tabs {
        overflow: visible;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .fd-tabs::-webkit-scrollbar {
        display: none;
    }

    .fd-tab {
        min-width: 0;
        flex: 1 1 auto;
        padding: 9px 4px;
        gap: 3px;
    }

    .fd-tab-icon {
        font-size: .95rem;
    }

    .fd-tab-label {
        font-size: .6rem;
        line-height: 1;
    }

    .fd-tab-badge {
        top: 3px;
        right: 5px;
        font-size: .55rem;
        padding: 1px 5px;
        min-width: 16px;
    }

    .fd-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 4px;
        padding: 8px;
        overflow: visible;
        border-bottom: 1px solid var(--gray);
    }

    .fd-stat {
        min-width: 0;
        padding: 7px 3px;
        border: 1px solid var(--gray);
        border-radius: 10px;
        background: rgba(255,255,255,.03);
    }

    .fd-stat:last-child {
        border-right: 1px solid var(--gray);
    }

    .fd-stat-val {
        font-size: .96rem;
        line-height: 1;
    }

    .fd-stat-lbl {
        font-size: .5rem;
        line-height: 1.05;
        letter-spacing: 0;
        text-transform: none;
    }

    .fd-subtabs {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 4px;
        padding: 8px;
        overflow: visible;
    }

    .fd-subtab {
        min-width: 0;
        min-height: 40px;
        padding: 7px 4px;
        white-space: normal;
        text-align: center;
        line-height: 1.05;
        gap: 3px;
        font-size: .62rem;
    }

    .fd-subtab i {
        display: none;
    }

    .fd-subtab .cnt {
        padding: 1px 5px;
        font-size: .58rem;
    }

    .fd-swipe-panel {
        min-height: auto;
        padding: 8px;
        overflow-y: visible;
    }

    .fd-card {
        padding: 10px;
        border-radius: 10px;
    }

    .fd-card.urgent::before {
        display: none;
    }

    .fd-card-head {
        flex-wrap: wrap;
        gap: 6px;
    }

    .fd-card-time {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: .66rem;
        padding: 2px 6px;
    }

    .fd-card-client {
        font-size: .8rem;
        margin-bottom: 5px;
    }

    .fd-card-item {
        font-size: .76rem;
    }

    .fd-card-item .qty {
        min-width: 22px;
        font-size: .66rem;
    }

    .fd-card-foot {
        margin-bottom: 8px;
    }

    .fd-card-price {
        font-size: .94rem;
    }

    .fd-card-type {
        font-size: .62rem;
        padding: 2px 8px;
    }

    .fd-pay-row {
        flex-wrap: wrap;
        align-items: flex-start;
        padding: 7px 9px;
        font-size: .72rem;
    }

    .fd-pay-tag {
        margin-left: auto;
    }

    .fd-card-foot {
        flex-wrap: wrap;
        gap: 6px;
        align-items: flex-start;
    }

    .fd-card-btns {
        flex-wrap: wrap;
    }

    .fd-card-btns > form,
    .fd-card-btns > button {
        flex: 1 1 calc(50% - 4px);
        min-width: 0;
    }

    .fd-card-btns .fd-cbtn {
        min-height: 44px;
        white-space: normal;
        line-height: 1.2;
        font-size: .74rem;
        padding: 8px 6px;
    }

    .fd-del-grid {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 8px;
    }

    .fd-int-board {
        padding: 8px;
        gap: 8px;
    }

    .fd-int-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 4px;
    }

    .fd-int-stat {
        padding: 7px 8px;
    }

    .fd-int-stat .lbl {
        font-size: .54rem;
        letter-spacing: 0;
    }

    .fd-int-stat .val {
        font-size: .9rem;
        margin-top: 2px;
    }

    .fd-int-filterbar {
        padding: 8px;
        gap: 6px;
    }

    .fd-int-filtertext {
        font-size: .66rem;
    }

    .fd-int-filterbtn {
        width: 100%;
        font-size: .64rem;
        padding: 7px 9px;
    }

    .fd-int-grid {
        grid-template-columns: 1fr;
    }

    .fd-del-head {
        flex-wrap: wrap;
        gap: 5px;
        padding: 10px 12px;
    }

    .fd-del-head .num {
        font-size: .88rem;
    }

    .fd-del-badge {
        max-width: 100%;
        white-space: normal;
        font-size: .62rem;
        padding: 3px 8px;
    }

    .fd-del-body {
        padding: 10px;
        gap: 8px;
    }

    .fd-del-addr,
    .fd-del-client,
    .fd-del-pay {
        font-size: .76rem;
    }

    .fd-del-driver,
    .fd-del-waiting {
        padding: 8px 10px;
        gap: 8px;
    }

    .fd-del-driver .avatar {
        width: 32px;
        height: 32px;
        font-size: .95rem;
    }

    .fd-agenda-card {
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 8px;
    }

    .fd-del-assign-form {
        grid-template-columns: 1fr;
    }

    .fd-agenda-time {
        min-width: 0;
        text-align: left;
    }

    .fd-agenda-total {
        margin-left: auto;
    }

    .fd-hist-filters {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .fd-hist-filters select,
    .fd-hist-filters input {
        width: 100%;
        min-width: 0;
    }

    .fd-hist-field {
        min-width: 0;
    }

    .fd-hist-actions {
        width: 100%;
    }

    .fd-hist-apply {
        width: 100%;
    }

    .fd-hist-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        padding: 10px 12px;
    }

    .fd-hist-num,
    .fd-hist-total {
        min-width: 0;
        text-align: left;
    }

    .fd-status {
        align-self: flex-start;
    }

    .fd-modal-box {
        width: calc(100% - 20px);
        padding: 20px 14px;
    }

    .fd-stat-modal .fd-modal-box {
        max-width: 100%;
        padding: 18px 14px;
    }

    .fd-stat-modal-head {
        align-items: stretch;
    }

    .fd-stat-detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .fd-stat-detail-number,
    .fd-stat-detail-total {
        min-width: 0;
    }

    .fd-code-inputs {
        gap: 8px;
    }

    .fd-code-inputs input {
        width: 50px;
        height: 58px;
        font-size: 1.5rem;
    }
}

@media (max-width: 420px) {
    .fd-actions {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .fd-subtab {
        font-size: .56rem;
        gap: 2px;
        padding: 7px 3px;
    }

    .fd-subtab .cnt {
        font-size: .54rem;
        padding: 1px 4px;
    }

    .fd-tab {
        min-width: 0;
        padding: 8px 3px;
    }

    .fd-card-btns > form,
    .fd-card-btns > button {
        flex-basis: 100%;
    }

    .fd-code-inputs input {
        width: 44px;
        height: 54px;
        font-size: 1.35rem;
    }

    .fd-del-assign-form {
        grid-template-columns: 1fr;
    }

    .fd-del-assign-form .fd-cbtn {
        width: 100%;
        min-width: 0;
    }
}
</style>
@endpush

@section('content')
@php
    $activeTab = $activeTab ?? request('tab', 'kitchen');
    $pendingCount = $orders['pending']->count();
    $acceptedCount = $orders['accepted']->count();
    $preparingCount = $orders['preparing']->count();
    $readyCount = $orders['ready']->count();
    $totalKitchen = $pendingCount + $acceptedCount + $preparingCount + $readyCount;
    $deliveryCount = ($deliveryOrders ?? collect())->count();
    $deliverySyncKey = $deliverySyncKey ?? '';
    $foodIsOpen = (bool) ($prestataire->food_is_open ?? true);
    $toggleOpenRoute = \Illuminate\Support\Facades\Route::has('prestataire.food-orders.toggle-open')
        ? route('prestataire.food-orders.toggle-open')
        : null;
    $scheduledOrders = $scheduledOrders ?? collect();
    $scheduledCount = $scheduledOrders->count();
    $statDetails = $statDetails ?? [];
@endphp

<div class="fd">

    {{-- ═══════ HEADER ═══════ --}}
    <div class="fd-header">
        <div class="fd-header-left">
            <div class="fd-logo"><i class="fas fa-fire-burner"></i></div>
            <div>
                <div class="fd-header-title">🍳 Cuisine Live</div>
                <div class="fd-header-meta">
                    <div class="fd-live"><span class="dot"></span> En direct</div>
                    <div class="fd-clock" id="fdClock">--:--:--</div>
                </div>
            </div>
        </div>
        <div class="fd-actions">
            <button class="fd-btn sound" id="fdSoundBtn" title="Son" type="button">
                <i class="fas fa-bell" id="fdSoundIcon"></i>
                <span class="fd-txt-desktop">Son</span><span class="fd-txt-mobile">Son</span>
            </button>
            <button class="fd-btn open {{ $foodIsOpen ? 'is-open' : 'is-closed' }}"
                    id="fdToggleOpenBtn"
                    type="button"
                    @if(!$toggleOpenRoute) disabled @endif
                    title="{{ $foodIsOpen ? 'Cliquez pour fermer le restaurant' : 'Cliquez pour ouvrir le restaurant' }}">
                <span class="fd-open-dot"></span>
                <span id="fdToggleOpenText">{{ $foodIsOpen ? 'Ouvert' : 'Fermé' }}</span>
            </button>
            <button class="fd-btn refresh" id="fdRefreshBtn" title="Actualiser" type="button">
                <i class="fas fa-sync-alt"></i><span class="fd-txt-desktop">Actualiser</span><span class="fd-txt-mobile">Actu</span>
            </button>
            <a href="{{ route('prestataire.food-orders.internal-map') }}" class="fd-btn mnu" title="Carte interne">
                <i class="fas fa-map-marked-alt"></i><span class="fd-txt-desktop">Carte interne</span><span class="fd-txt-mobile">Carte</span>
            </a>
            <a href="{{ route('prestataire.drivers.index') }}" class="fd-btn mnu" title="Gestion des livreurs">
                <i class="fas fa-users"></i><span class="fd-txt-desktop">Livreurs</span><span class="fd-txt-mobile">Livreurs</span>
            </a>
            <a href="{{ route('prestataire.food-delivery.index') }}" class="fd-btn ldv" title="Paramètres livraison">
                <i class="fas fa-motorcycle"></i><span class="fd-txt-desktop">Livraisons</span><span class="fd-txt-mobile">Livraison</span>
            </a>
            <a href="{{ route('prestataire.food-products.index') }}" class="fd-btn mnu" title="Gérer le menu">
                <i class="fas fa-utensils"></i><span class="fd-txt-desktop">Menu</span><span class="fd-txt-mobile">Menu</span>
            </a>
            <a href="{{ route('prestataire.dashboard') }}" class="fd-btn hm" title="Accueil">
                <i class="fas fa-home"></i>
                <span class="fd-txt-desktop">Accueil</span><span class="fd-txt-mobile">Accueil</span>
            </a>
        </div>
    </div>

    {{-- ═══════ STATS BAR ═══════ --}}
    <div class="fd-stats">
        <div class="fd-stat" role="button" tabindex="0" data-stat-detail="today">
            <div class="fd-stat-val green" id="fdStatRevToday">{{ number_format($paymentStats['revenue_today'] ?? 0, 2) }}€</div>
            <div class="fd-stat-lbl"><span class="fd-m-only">CA jour</span><span class="fd-d-only">CA aujourd'hui</span></div>
        </div>
        <div class="fd-stat" role="button" tabindex="0" data-stat-detail="paid">
            <div class="fd-stat-val blue" id="fdStatPaidCount">{{ $paymentStats['paid_count_today'] ?? 0 }}</div>
            <div class="fd-stat-lbl"><span class="fd-m-only">Payees</span><span class="fd-d-only">Commandes payees</span></div>
        </div>
        <div class="fd-stat" role="button" tabindex="0" data-stat-detail="pending">
            <div class="fd-stat-val orange" id="fdStatPending">{{ number_format($paymentStats['pending_payments'] ?? 0, 2) }}€</div>
            <div class="fd-stat-lbl" id="fdStatPendingLbl"><span class="fd-m-only">Att. ({{ $paymentStats['pending_count'] ?? 0 }})</span><span class="fd-d-only">En attente ({{ $paymentStats['pending_count'] ?? 0 }})</span></div>
        </div>
        <div class="fd-stat" role="button" tabindex="0" data-stat-detail="month">
            <div class="fd-stat-val green" id="fdStatRevMonth">{{ number_format($paymentStats['revenue_month'] ?? 0, 2) }}€</div>
            <div class="fd-stat-lbl"><span class="fd-m-only">CA mois</span><span class="fd-d-only">CA ce mois</span></div>
        </div>
        <div class="fd-stat" role="button" tabindex="0" data-stat-detail="kitchen">
            <div class="fd-stat-val purple" id="fdStatKitchen">{{ $totalKitchen }}</div>
            <div class="fd-stat-lbl"><span class="fd-m-only">Cuisine</span><span class="fd-d-only">En cuisine</span></div>
        </div>
    </div>

    {{-- ═══════ MAIN TABS ═══════ --}}
    <div class="fd-tabs">
        <div class="fd-tab {{ $activeTab === 'kitchen' ? 'active' : '' }}" data-tab="kitchen" role="button" tabindex="0">
            <span class="fd-tab-icon"><i class="fas fa-fire"></i></span>
            @if($totalKitchen > 0)<span class="fd-tab-badge">{{ $totalKitchen }}</span>@endif
            <span class="fd-tab-label">Cuisine</span>
        </div>
        <div class="fd-tab {{ $activeTab === 'delivery' ? 'active' : '' }}" data-tab="delivery" role="button" tabindex="0">
            <span class="fd-tab-icon"><i class="fas fa-motorcycle"></i></span>
            @if($deliveryCount > 0)<span class="fd-tab-badge">{{ $deliveryCount }}</span>@endif
            <span class="fd-tab-label"><span class="fd-m-only">Livr.</span><span class="fd-d-only">Livraisons</span></span>
        </div>
        <div class="fd-tab {{ $activeTab === 'agenda' ? 'active' : '' }}" data-tab="agenda" role="button" tabindex="0">
            <span class="fd-tab-icon"><i class="fas fa-calendar-alt"></i></span>
            @if($scheduledCount > 0)<span class="fd-tab-badge">{{ $scheduledCount }}</span>@endif
            <span class="fd-tab-label">Agenda</span>
        </div>
        <div class="fd-tab {{ $activeTab === 'history' ? 'active' : '' }}" data-tab="history" role="button" tabindex="0">
            <span class="fd-tab-icon"><i class="fas fa-history"></i></span>
            <span class="fd-tab-label"><span class="fd-m-only">Histo</span><span class="fd-d-only">Historique</span></span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB: CUISINE
         ═══════════════════════════════════════════ --}}
    <div class="fd-content {{ $activeTab === 'kitchen' ? 'active' : '' }}" id="fd-kitchen">

        {{-- Sub tabs mobile --}}
        <div class="fd-subtabs">
            <div class="fd-subtab pending active" data-panel="0" role="button" tabindex="0">
                <i class="fas fa-clock"></i> <span class="fd-m-only">Att.</span><span class="fd-d-only">Attente</span> <span class="cnt">{{ $pendingCount }}</span>
            </div>
            <div class="fd-subtab accepted" data-panel="1" role="button" tabindex="0">
                <i class="fas fa-check"></i> <span class="fd-m-only">OK</span><span class="fd-d-only">Acceptées</span> <span class="cnt">{{ $acceptedCount }}</span>
            </div>
            <div class="fd-subtab preparing" data-panel="2" role="button" tabindex="0">
                <i class="fas fa-fire-alt"></i> <span class="fd-m-only">Prep.</span><span class="fd-d-only">Prepa</span> <span class="cnt">{{ $preparingCount }}</span>
            </div>
            <div class="fd-subtab ready" data-panel="3" role="button" tabindex="0">
                <i class="fas fa-check-circle"></i> <span class="fd-m-only">Pretes</span><span class="fd-d-only">Prêtes</span> <span class="cnt">{{ $readyCount }}</span>
            </div>
        </div>

        {{-- ── SWIPE PANELS (Mobile) ── --}}
        <div class="fd-swipe" id="fdSwipe">
            <div class="fd-swipe-wrap" id="fdSwipeWrap">

                {{-- PANEL 0: EN ATTENTE --}}
                <div class="fd-swipe-panel">
                    <div class="fd-order-list">
                        @forelse($orders['pending'] as $order)
                            @include('prestataire.food-orders._order_card', ['order' => $order, 'panel' => 'pending'])
                        @empty
                            <div class="fd-empty"><div class="fd-empty-ico">☕</div><p>Aucune commande en attente</p><p class="fd-empty-sub">Les nouvelles commandes apparaîtront ici</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- PANEL 1: ACCEPTÉES --}}
                <div class="fd-swipe-panel">
                    <div class="fd-order-list">
                        @forelse($orders['accepted'] as $order)
                            @include('prestataire.food-orders._order_card', ['order' => $order, 'panel' => 'accepted'])
                        @empty
                            <div class="fd-empty"><div class="fd-empty-ico">📋</div><p>Aucune commande acceptée</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- PANEL 2: EN PRÉPARATION --}}
                <div class="fd-swipe-panel">
                    <div class="fd-order-list">
                        @forelse($orders['preparing'] as $order)
                            @include('prestataire.food-orders._order_card', ['order' => $order, 'panel' => 'preparing'])
                        @empty
                            <div class="fd-empty"><div class="fd-empty-ico">👨‍🍳</div><p>Rien en préparation</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- PANEL 3: PRÊTES --}}
                <div class="fd-swipe-panel">
                    <div class="fd-order-list">
                        @forelse($orders['ready'] as $order)
                            @include('prestataire.food-orders._order_card', ['order' => $order, 'panel' => 'ready'])
                        @empty
                            <div class="fd-empty"><div class="fd-empty-ico">✨</div><p>Toutes les commandes sont servies !</p></div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        {{-- ── DESKTOP KANBAN ── --}}
        <div class="fd-kanban">
            @foreach(['pending' => ['⏳ En attente', $pendingCount], 'accepted' => ['✓ Acceptées', $acceptedCount], 'preparing' => ['🔥 En cuisine', $preparingCount], 'ready' => ['✅ Prêtes', $readyCount]] as $key => [$label, $count])
            <div class="fd-col">
                <div class="fd-col-head {{ $key }}">{{ $label }} <span class="cnt">{{ $count }}</span></div>
                <div class="fd-col-body">
                    <div class="fd-order-list">
                        @forelse($orders[$key] as $order)
                            @include('prestataire.food-orders._order_card', ['order' => $order, 'panel' => $key])
                        @empty
                            <div class="fd-empty"><div class="fd-empty-ico">
                                @switch($key)
                                    @case('pending') ☕ @break
                                    @case('accepted') 📋 @break
                                    @case('preparing') 👨‍🍳 @break
                                    @case('ready') ✨ @break
                                @endswitch
                            </div>
                            <p>
                                @switch($key)
                                    @case('pending') Aucune commande en attente @break
                                    @case('accepted') Aucune commande acceptée @break
                                    @case('preparing') Aucune préparation en cours @break
                                    @case('ready') Aucune commande prête @break
                                @endswitch
                            </p>
                            <p class="fd-empty-sub">Les nouvelles commandes apparaîtront ici</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB: LIVRAISONS
         ═══════════════════════════════════════════ --}}
    <div class="fd-content {{ $activeTab === 'delivery' ? 'active' : '' }}" id="fd-delivery">
        @php
            $internalBoard = $internalBoard ?? ['fleet' => collect(), 'stats' => []];
            $internalFleet = $internalBoard['fleet'] ?? collect();
            $internalStats = $internalBoard['stats'] ?? [];
            $internalDriverOptions = collect($internalFleet)
                ->map(function ($driverRoute) {
                    return [
                        'id' => (int) ($driverRoute['driver_id'] ?? 0),
                        'name' => (string) ($driverRoute['driver_name'] ?? 'Livreur'),
                    ];
                })
                ->filter(fn ($d) => $d['id'] > 0)
                ->unique('id')
                ->values();
        @endphp

        @if(($internalStats['drivers_total'] ?? 0) > 0)
            <div class="fd-int-board">
                <div class="fd-int-stats">
                    <button type="button" class="fd-int-stat active" data-filter="all" data-fd-internal-filter="all">
                        <div class="lbl">Livreurs internes</div>
                        <div class="val">{{ $internalStats['drivers_total'] ?? 0 }}</div>
                    </button>
                    <button type="button" class="fd-int-stat" data-filter="available" data-fd-internal-filter="available">
                        <div class="lbl">Disponibles</div>
                        <div class="val">{{ $internalStats['drivers_available'] ?? 0 }}</div>
                    </button>
                    <button type="button" class="fd-int-stat" data-filter="busy" data-fd-internal-filter="busy">
                        <div class="lbl">En mission</div>
                        <div class="val">{{ $internalStats['drivers_on_mission'] ?? 0 }}</div>
                    </button>
                    <button type="button" class="fd-int-stat" data-filter="orders" data-fd-internal-filter="orders">
                        <div class="lbl">Commandes actives</div>
                        <div class="val">{{ $internalStats['active_orders_total'] ?? 0 }}</div>
                    </button>
                    <button type="button" class="fd-int-stat" data-filter="points" data-fd-internal-filter="points">
                        <div class="lbl">Points restants</div>
                        <div class="val">{{ $internalStats['remaining_points_total'] ?? 0 }}</div>
                    </button>
                </div>

                <div class="fd-int-filterbar" id="fdInternalFilterBar" hidden>
                    <span class="fd-int-filtertext" id="fdInternalFilterText"></span>
                    <button type="button" class="fd-int-filterbtn" data-fd-internal-filter="all">Tout afficher</button>
                </div>

                <div class="fd-int-grid" id="fdInternalGrid">
                    @foreach($internalFleet as $driverRoute)
                        <div
                            class="fd-int-card"
                            data-available="{{ !empty($driverRoute['is_available']) ? '1' : '0' }}"
                            data-active-orders="{{ (int) ($driverRoute['active_orders_count'] ?? 0) }}"
                            data-remaining-points="{{ (int) ($driverRoute['remaining_points_count'] ?? 0) }}"
                        >
                            <div class="fd-int-head">
                                <div class="fd-int-driver">🛵 {{ $driverRoute['driver_name'] ?? 'Livreur' }}</div>
                                <span class="fd-int-status {{ ($driverRoute['active_orders_count'] ?? 0) > 0 ? 'busy' : '' }}">
                                    {{ ($driverRoute['active_orders_count'] ?? 0) > 0 ? 'En tournee' : 'Disponible' }}
                                </span>
                            </div>
                            <div class="fd-int-body">
                                <div class="fd-int-meta">
                                    <span class="fd-int-pill">{{ $driverRoute['active_orders_count'] ?? 0 }} commande(s)</span>
                                    <span class="fd-int-pill">{{ $driverRoute['remaining_points_count'] ?? 0 }} point(s) restant(s)</span>
                                    @if(($driverRoute['remaining_eta_minutes'] ?? 0) > 0)
                                        <span class="fd-int-pill">ETA ~ {{ $driverRoute['remaining_eta_minutes'] }} min</span>
                                    @endif
                                </div>

                                @if(!empty($driverRoute['driver_phone']))
                                    <a class="fd-int-phone" href="tel:{{ $driverRoute['driver_phone'] }}">📞 {{ $driverRoute['driver_phone'] }}</a>
                                @endif

                                @if(($driverRoute['remaining_points_count'] ?? 0) > 0)
                                    <div class="fd-int-route">
                                        @foreach(($driverRoute['route_points'] ?? []) as $index => $point)
                                            <div class="fd-int-point {{ $point['kind'] ?? '' }}">
                                                <span class="fd-int-n">{{ $index + 1 }}</span>
                                                <div>
                                                    <strong>{{ $point['label'] ?? 'Point de route' }}</strong>
                                                    <div>#{{ $point['order_number'] ?? '-' }} · {{ $point['address'] ?? 'Adresse non renseignee' }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="fd-int-empty">Aucun point en attente pour ce livreur.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="fd-int-empty" id="fdInternalFilterEmpty" hidden>Aucun livreur ne correspond a ce filtre.</div>
            </div>
        @endif

        <div class="fd-del-grid" id="fdDeliveryGrid">
            @forelse($deliveryOrders ?? [] as $order)
                <div class="fd-del-card">
                    <div class="fd-del-head">
                        <span class="num">#{{ $order->order_number }}</span>
                        <span class="fd-del-badge">
                            @switch($order->delivery_status ?? 'pending')
                                @case('assigned') 🛵 Livreur assigné @break
                                @case('picked_up') 📦 Récupérée @break
                                @case('in_transit') 🚀 En route @break
                                @case('delivered') ✅ Livrée @break
                                @default ⏳ En attente livreur
                            @endswitch
                        </span>
                    </div>
                    <div class="fd-del-body">
                        <div class="fd-del-client">👤 {{ $order->client->name ?? 'Client' }} · {{ number_format($order->total, 2) }}€</div>
                        <div class="fd-del-addr">📍 {{ $order->delivery_address ?? 'Adresse non renseignée' }}</div>

                        {{-- Payment info --}}
                        @php
                            $delPolicy = method_exists($order, 'getPaymentPolicy') ? ((array) ($order->getPaymentPolicy() ?? [])) : [];
                            $delPType  = $delPolicy['type'] ?? 'cash';
                            $delPaid   = ($order->payment_status === 'paid');
                        @endphp
                        <div class="fd-del-pay">
                            @if($delPType === 'cash')
                                💵 Espèces · @if($delPaid) <span style="color:#86efac">✓ Encaissé</span> @else {{ number_format($order->total, 2) }}€ à encaisser @endif
                            @elseif($delPType === 'deposit')
                                🏦 Acompte {{ $delPolicy['percent'] }}% en ligne · @if($delPaid) <span style="color:#86efac">✓ Payé</span> @else Reste {{ number_format($order->total - round($order->total * $delPolicy['percent']/100, 2), 2) }}€ en espèces @endif
                            @elseif($delPType === 'full_prepay')
                                💳 Payé en ligne · @if($delPaid) <span style="color:#86efac">✓ {{ number_format($order->total, 2) }}€</span> @else <span style="color:#fca5a5">⏳ En attente</span> @endif
                            @endif
                        </div>

                        @if($order->driver)
                            <div class="fd-del-driver">
                                <div class="avatar">🛵</div>
                                <div class="info">
                                    <strong>{{ $order->driver->user->name ?? 'Livreur' }}</strong>
                                    @if($order->driver->user->phone ?? null)
                                        <span style="font-size:.75rem;opacity:.8">📞 {{ $order->driver->user->phone }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="fd-del-waiting">
                                @if(($prestataire->delivery_mode ?? 'both') === 'internal')
                                    <i class="fas fa-user-clock"></i> En attente d'affectation d'un livreur interne.
                                @else
                                    <i class="fas fa-spinner fa-spin"></i> Recherche d'un livreur en cours...
                                @endif
                            </div>
                            @php
                                $canAssignFromCuisine = in_array((string) ($order->status ?? ''), ['accepted', 'preparing', 'ready'], true)
                                    && in_array((string) ($order->delivery_status ?? 'pending'), ['pending', 'assigned', 'self_delivery', ''], true);
                            @endphp
                            @if($canAssignFromCuisine)
                                @if($internalDriverOptions->isNotEmpty())
                                    <form method="POST" action="{{ route('prestataire.food-orders.assign-driver', $order) }}" class="fd-del-assign-form">
                                        @csrf
                                        <select name="driver_id" required class="fd-del-assign-select">
                                            <option value="">Choisir un livreur interne</option>
                                            @foreach($internalDriverOptions as $driverOption)
                                                <option value="{{ $driverOption['id'] }}">{{ $driverOption['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="fd-cbtn code">Assigner</button>
                                    </form>
                                @else
                                    <div class="fd-del-waiting" style="margin-top:8px;">
                                        <i class="fas fa-user-plus"></i> Aucun livreur interne. Ajoutez-en dans "Livreurs".
                                    </div>
                                @endif
                            @else
                                <div class="fd-del-waiting" style="margin-top:8px;">
                                    <i class="fas fa-lock"></i> Affectation indisponible pour cet état de commande.
                                </div>
                            @endif
                        @endif

                        <div class="fd-del-actions">
                            <button class="fd-cbtn code" type="button" data-fd-open-code="{{ $order->id }}">🔐 Vérifier code</button>
                            @if($delPType === 'cash' && !$delPaid)
                                <form method="POST" action="{{ route('prestataire.food-orders.confirm-cash', $order) }}" style="flex:1" data-confirm="Confirmer encaissement espèces ?">
                                    @csrf
                                    <button type="submit" class="fd-cbtn cash-confirm">
                                        <i class="fas fa-coins"></i> Encaisser
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="fd-empty" style="grid-column:1/-1"><div class="fd-empty-ico">🛵</div><p>Aucune livraison en cours</p><p class="fd-empty-sub">Les livraisons actives apparaîtront ici</p></div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB: AGENDA (commandes planifiées)
         ═══════════════════════════════════════════ --}}
    <div class="fd-content {{ $activeTab === 'agenda' ? 'active' : '' }}" id="fd-agenda">
        <div class="fd-agenda">
            @if($scheduledOrders->isEmpty())
                <div class="fd-empty"><div class="fd-empty-ico">📅</div><p>Aucune commande planifiée</p><p class="fd-empty-sub">Les commandes avec une date future apparaîtront dans l'agenda après acceptation</p></div>
            @else
                <div class="fd-agenda-list">
                    @php $lastDay = ''; @endphp
                    @foreach($scheduledOrders as $order)
                        @php $day = $order->requested_at->translatedFormat('l d F Y'); @endphp
                        @if($day !== $lastDay)
                            <div class="fd-agenda-day">📅 {{ $day }}</div>
                            @php $lastDay = $day; @endphp
                        @endif
                        <div class="fd-agenda-card" data-fd-order-link data-href="{{ route('prestataire.food-orders.show', $order) }}" role="link" tabindex="0">
                            <div class="fd-agenda-time">{{ $order->requested_at->format('H\hi') }}</div>
                            <div class="fd-agenda-detail">
                                <div class="fd-agenda-client">#{{ $order->order_number }} · {{ $order->client->name ?? 'Client' }}</div>
                                <div class="fd-agenda-items">
                                    @foreach($order->items->take(3) as $item)
                                        {{ $item->quantity }}x {{ $item->product_name }}@if(!$loop->last), @endif
                                    @endforeach
                                    @if($order->items->count() > 3) +{{ $order->items->count() - 3 }} @endif
                                </div>
                            </div>
                            <div class="fd-agenda-total">{{ number_format($order->total, 2) }}€</div>
                            <span class="fd-card-type {{ $order->delivery_type }}">{{ $order->delivery_type === 'delivery' ? '🛵 Livr.' : '🏪 Emp.' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         TAB: HISTORIQUE
         ═══════════════════════════════════════════ --}}
    <div class="fd-content {{ $activeTab === 'history' ? 'active' : '' }}" id="fd-history">
            <div class="fd-history">
                <div class="fd-hist-filters">
                    <label class="fd-hist-field">
                        <span>Statut</span>
                        <select id="fdStatusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>✅ Livrées</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>✓ Terminées</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>✕ Annulées</option>
                    </select>
                </label>
                    <label class="fd-hist-field">
                        <span>Commande</span>
                        <input
                            type="search"
                            id="fdSearchFilter"
                            value="{{ request('search', '') }}"
                            placeholder="#CMD..."
                        >
                    </label>
                    <label class="fd-hist-field">
                        <span>Date</span>
                        <input type="date" id="fdDateFilter" value="{{ request('date', '') }}">
                    </label>
                    <div class="fd-hist-actions">
                        <button type="button" class="fd-hist-apply" id="fdHistApplyBtn">Filtrer</button>
                    </div>
                </div>
                <div class="fd-hist-list">
                    @forelse($historyOrders ?? [] as $order)
                        <div class="fd-hist-item" data-fd-order-link data-href="{{ route('prestataire.food-orders.show', $order) }}" role="link" tabindex="0">
                            <span class="fd-hist-num">#{{ $order->order_number }}</span>
                        <span class="fd-hist-info">
                            {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->client->name ?? 'Client' }}
                            @if($order->delivery_type === 'delivery') · 🛵 @else · 🏪 @endif
                        </span>
                        <span class="fd-hist-total">{{ number_format($order->total, 2) }}€</span>
                        <span class="fd-status {{ $order->status }}">
                            @switch($order->status)
                                @case('delivered') ✅ Livrée @break
                                @case('completed') ✓ Terminée @break
                                @case('cancelled') ✕ Annulée @break
                                @default {{ ucfirst($order->status) }}
                            @endswitch
                        </span>
                    </div>
                @empty
                    <div class="fd-empty"><div class="fd-empty-ico">📜</div><p>Aucun historique trouvé</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════ CODE MODAL ═══════ --}}
    <div class="fd-modal" id="fdCodeModal">
        <div class="fd-modal-box">
            <h3>🔐 Code de vérification</h3>
            <p class="fd-modal-sub">Demandez le code à 4 chiffres au client pour valider la remise</p>
            <div class="fd-code-inputs">
                <input type="text" maxlength="1" id="fc1" inputmode="numeric" data-fd-code-input data-next="2">
                <input type="text" maxlength="1" id="fc2" inputmode="numeric" data-fd-code-input data-next="3">
                <input type="text" maxlength="1" id="fc3" inputmode="numeric" data-fd-code-input data-next="4">
                <input type="text" maxlength="1" id="fc4" inputmode="numeric" data-fd-code-input data-next="submit">
            </div>
            <div class="fd-code-error" id="fdCodeError">Code incorrect, réessayez</div>
            <div class="fd-modal-btns">
                <button class="cancel" id="fdCodeCancelBtn" type="button">Annuler</button>
                <button class="confirm" id="fdConfirmBtn" type="button">Valider</button>
            </div>
        </div>
    </div>

    <div class="fd-modal fd-stat-modal" id="fdStatModal">
        <div class="fd-modal-box">
            <div class="fd-stat-modal-head">
                <div>
                    <h3 id="fdStatModalTitle">Detail</h3>
                    <p class="fd-modal-sub" id="fdStatModalSubtitle"></p>
                    <div class="fd-stat-modal-meta">
                        <span class="fd-stat-chip" id="fdStatModalCount">0 commande</span>
                        <span class="fd-stat-chip" id="fdStatModalAmount">0.00€</span>
                    </div>
                </div>
                <button type="button" class="fd-stat-modal-close" id="fdStatModalCloseBtn">✕</button>
            </div>
            <div class="fd-stat-detail-list" id="fdStatModalList"></div>
            <div class="fd-stat-note" id="fdStatModalNote" hidden>Seules les 100 premieres commandes sont affichees.</div>
        </div>
    </div>

</div>

<audio id="fdNotifSound" preload="auto"><source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg"></audio>
@endsection

@push('scripts')
<script>
/* ═══════ STATE ═══════ */
let fdSoundOn = localStorage.getItem('orderSound') !== 'false';
let fdLastCount = {{ $pendingCount }};
let fdLastDeliveryCount = {{ (int) $deliveryCount }};
let fdLastDeliverySyncKey = @json($deliverySyncKey);
const fdStatDetails = @json($statDetails);
let fdCurrentOrder = null;
let fdCurrentPanel = 0;
let fdDeliveryFocusTimeout = null;

/* ═══════ CLOCK ═══════ */
(function fdClock() {
    const el = document.getElementById('fdClock');
    if (el) el.textContent = new Date().toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    setTimeout(fdClock, 1000);
})();

/* ═══════ MAIN TABS ═══════ */
function fdSwitchTab(t) {
    document.querySelectorAll('.fd-tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.fd-content').forEach(el => el.classList.remove('active'));
    document.querySelector(`.fd-tab[data-tab="${t}"]`)?.classList.add('active');
    document.getElementById('fd-' + t)?.classList.add('active');
    const url = new URL(window.location);
    url.searchParams.set('tab', t);
    window.history.replaceState({}, '', url);
}

function fdStatKeyOpen(event, key) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        fdOpenStatDetail(key);
    }
}

/* Restore active tab from URL */
(function() {
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (tab && ['kitchen','delivery','agenda','history'].includes(tab)) fdSwitchTab(tab);
})();

/* ═══════ SUB PANELS (Mobile) ═══════ */
function fdPanel(index) {
    fdCurrentPanel = index;
    document.querySelectorAll('.fd-subtab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.fd-subtab')[index]?.classList.add('active');
    document.getElementById('fdSwipeWrap').style.transform = `translateX(-${index * 100}%)`;
}

/* ═══════ SWIPE GESTURES ═══════ */
(function() {
    const container = document.getElementById('fdSwipe');
    if (!container) return;
    let startX = 0, startY = 0, dragging = false;

    container.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        dragging = true;
    }, { passive: true });

    container.addEventListener('touchmove', e => {
        if (!dragging) return;
        const dx = e.touches[0].clientX - startX;
        const dy = e.touches[0].clientY - startY;
        if (Math.abs(dx) > Math.abs(dy)) e.preventDefault();
    }, { passive: false });

    container.addEventListener('touchend', e => {
        if (!dragging) return;
        dragging = false;
        const diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0 && fdCurrentPanel < 3) fdPanel(fdCurrentPanel + 1);
            else if (diff < 0 && fdCurrentPanel > 0) fdPanel(fdCurrentPanel - 1);
        }
    }, { passive: true });
})();

/* ═══════ SOUND ═══════ */
function fdToggleSound() {
    fdSoundOn = !fdSoundOn;
    localStorage.setItem('orderSound', fdSoundOn);
    document.getElementById('fdSoundIcon').className = fdSoundOn ? 'fas fa-bell' : 'fas fa-bell-slash';
    document.getElementById('fdSoundBtn').classList.toggle('muted', !fdSoundOn);
}
(function() {
    if (!fdSoundOn) {
        document.getElementById('fdSoundIcon').className = 'fas fa-bell-slash';
        document.getElementById('fdSoundBtn').classList.add('muted');
    }
})();

/* ═══════ OPEN/CLOSED STATUS ═══════ */
function fdToggleOpenStatus() {
    const btn = document.getElementById('fdToggleOpenBtn');
    const text = document.getElementById('fdToggleOpenText');
    if (!btn || !text) return;
    const toggleOpenUrl = @json($toggleOpenRoute);
    if (!toggleOpenUrl) {
        alert('Action indisponible.');
        return;
    }

    btn.disabled = true;
    btn.style.opacity = '0.6';

    fetch(toggleOpenUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(async response => {
        const isJson = (response.headers.get('content-type') || '').includes('application/json');
        const payload = isJson ? await response.json() : { success: false, message: 'Réponse invalide' };
        if (!response.ok) {
            const msg = payload?.message || ('Erreur HTTP ' + response.status);
            throw new Error(msg);
        }
        return payload;
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Impossible de changer le statut.');
        }

        const isOpen = !!data.is_open;
        btn.classList.remove('is-open', 'is-closed');
        btn.classList.add(isOpen ? 'is-open' : 'is-closed');
        btn.title = isOpen ? 'Cliquez pour fermer le restaurant' : 'Cliquez pour ouvrir le restaurant';
        text.textContent = isOpen ? 'Ouvert' : 'Fermé';
    })
    .catch(error => {
        console.error(error);
        alert(error.message || 'Erreur de connexion');
    })
    .finally(() => {
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}

function fdStatusText(status) {
    switch ((status || '').toLowerCase()) {
        case 'pending': return 'En attente';
        case 'accepted': return 'Acceptee';
        case 'preparing': return 'En preparation';
        case 'ready': return 'Prete';
        case 'completed': return 'Terminee';
        case 'delivered': return 'Livree';
        case 'cancelled': return 'Annulee';
        default: return status || 'Statut inconnu';
    }
}

function fdDeliveryTypeText(type) {
    return type === 'delivery' ? 'Livraison' : 'Retrait';
}

function fdOpenStatDetail(key) {
    const detail = fdStatDetails?.[key];
    if (!detail) return;

    const modal = document.getElementById('fdStatModal');
    const title = document.getElementById('fdStatModalTitle');
    const subtitle = document.getElementById('fdStatModalSubtitle');
    const count = document.getElementById('fdStatModalCount');
    const amount = document.getElementById('fdStatModalAmount');
    const list = document.getElementById('fdStatModalList');
    const note = document.getElementById('fdStatModalNote');

    title.textContent = detail.title || 'Detail';
    subtitle.textContent = detail.subtitle || '';
    count.textContent = `${detail.total_count || 0} commande(s)`;
    amount.textContent = fdFmt(detail.total_amount || 0);
    note.hidden = !detail.truncated;

    const items = Array.isArray(detail.items) ? detail.items : [];
    if (!items.length) {
        list.innerHTML = '<div class="fd-stat-empty">Aucune commande pour ce chiffre.</div>';
    } else {
        list.innerHTML = items.map((item) => {
            const dateParts = [item.date_prefix || '', item.date || ''].filter(Boolean).join(' ');
            const meta = [
                dateParts,
                fdDeliveryTypeText(item.delivery_type),
                fdStatusText(item.status),
            ].filter(Boolean).join(' · ');

            return `
                <a class="fd-stat-detail-item" href="${item.url || '#'}">
                    <span class="fd-stat-detail-number">#${item.number || '-'}</span>
                    <span class="fd-stat-detail-main">
                        <span class="fd-stat-detail-client">${item.client || 'Client'}</span>
                        <span class="fd-stat-detail-meta">${meta}</span>
                    </span>
                    <span class="fd-stat-detail-total">${fdFmt(item.total || 0)}</span>
                </a>
            `;
        }).join('');
    }

    modal.classList.add('active');
}

function fdCloseStatDetail() {
    document.getElementById('fdStatModal').classList.remove('active');
}

/* ═══════ DELIVERY DETAIL FILTERS ═══════ */
function fdFilterInternalBoard(mode) {
    const cards = Array.from(document.querySelectorAll('.fd-int-card'));
    const stats = Array.from(document.querySelectorAll('.fd-int-stat[data-filter]'));
    const filterBar = document.getElementById('fdInternalFilterBar');
    const filterText = document.getElementById('fdInternalFilterText');
    const emptyState = document.getElementById('fdInternalFilterEmpty');
    const grid = document.getElementById('fdInternalGrid');
    const deliveryGrid = document.getElementById('fdDeliveryGrid');

    if (!cards.length || !stats.length) return;

    const labels = {
        available: 'livreurs disponibles',
        busy: 'livreurs en mission',
        orders: 'commandes actives',
        points: 'livreurs avec points restants',
    };

    stats.forEach((stat) => {
        stat.classList.toggle('active', stat.dataset.filter === mode);
    });

    if (mode === 'orders') {
        cards.forEach((card) => card.classList.remove('is-hidden'));
        if (filterBar && filterText) {
            filterBar.hidden = false;
            filterText.textContent = 'Detail : commandes actives ci-dessous';
        }
        if (emptyState) emptyState.hidden = true;
        sessionStorage.removeItem('fdInternalBoardFilter');

        if (deliveryGrid) {
            deliveryGrid.classList.add('fd-focus');
            deliveryGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (fdDeliveryFocusTimeout) clearTimeout(fdDeliveryFocusTimeout);
            fdDeliveryFocusTimeout = setTimeout(() => deliveryGrid.classList.remove('fd-focus'), 1400);
        }

        return;
    }

    let visibleCount = 0;
    cards.forEach((card) => {
        const isAvailable = card.dataset.available === '1';
        const activeOrders = Number(card.dataset.activeOrders || 0);
        const remainingPoints = Number(card.dataset.remainingPoints || 0);
        let visible = true;

        switch (mode) {
            case 'available':
                visible = isAvailable;
                break;
            case 'busy':
                visible = activeOrders > 0;
                break;
            case 'points':
                visible = remainingPoints > 0;
                break;
            default:
                visible = true;
                break;
        }

        card.classList.toggle('is-hidden', !visible);
        if (visible) visibleCount++;
    });

    if (mode === 'all') {
        if (filterBar && filterText) {
            filterBar.hidden = true;
            filterText.textContent = '';
        }
        if (emptyState) emptyState.hidden = true;
        sessionStorage.removeItem('fdInternalBoardFilter');
        return;
    }

    if (filterBar && filterText) {
        filterBar.hidden = false;
        filterText.textContent = 'Detail : ' + (labels[mode] || 'filtre') + ' (' + visibleCount + ')';
    }

    if (emptyState) {
        emptyState.hidden = visibleCount > 0;
    }

    sessionStorage.setItem('fdInternalBoardFilter', mode);
    grid?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

(function fdRestoreInternalBoardFilter() {
    const savedFilter = sessionStorage.getItem('fdInternalBoardFilter');
    if (savedFilter) fdFilterInternalBoard(savedFilter);
})();

/* ═══════ POLLING (30s) ═══════ */
function fdFmt(v) { return parseFloat(v || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + '€'; }

function fdUpdateStats(s) {
    if (!s) return;
    const el = (id) => document.getElementById(id);
    const pendingCount = s.pending_count ?? 0;
    el('fdStatRevToday').textContent = fdFmt(s.revenue_today);
    el('fdStatPaidCount').textContent = s.paid_count_today ?? 0;
    el('fdStatPending').textContent = fdFmt(s.pending_payments);
    el('fdStatPendingLbl').innerHTML = '<span class="fd-m-only">Att. (' + pendingCount + ')</span><span class="fd-d-only">En attente (' + pendingCount + ')</span>';
    el('fdStatRevMonth').textContent = fdFmt(s.revenue_month);
    if (s.kitchen !== undefined) el('fdStatKitchen').textContent = s.kitchen;
}

setInterval(() => {
    fetch('{{ route("prestataire.food-orders.new") }}')
        .then(r => r.json())
        .then(d => {
            // Mettre à jour les stats en live
            fdUpdateStats(d.stats);
            if (d.kitchen !== undefined) document.getElementById('fdStatKitchen').textContent = d.kitchen;
            // Son + reload si nouvelles commandes pending
            if (d.count > fdLastCount && fdSoundOn) document.getElementById('fdNotifSound').play().catch(()=>{});
            const deliveryCountChanged = Number(d.delivery_count ?? fdLastDeliveryCount) !== Number(fdLastDeliveryCount);
            const deliverySyncKey = String(d.delivery_sync_key || '');
            const deliverySyncChanged = deliverySyncKey !== '' && deliverySyncKey !== String(fdLastDeliverySyncKey || '');
            if (d.count !== fdLastCount || deliveryCountChanged || deliverySyncChanged) location.reload();
            fdLastCount = d.count;
            fdLastDeliveryCount = Number(d.delivery_count ?? fdLastDeliveryCount);
            fdLastDeliverySyncKey = deliverySyncKey || fdLastDeliverySyncKey;
        }).catch(() => {});
}, 30000);

/* ═══════ CODE MODAL ═══════ */
function fdOpenCode(orderId) {
    fdCurrentOrder = orderId;
    document.getElementById('fdCodeModal').classList.add('active');
    ['fc1','fc2','fc3','fc4'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fdCodeError').style.display = 'none';
    setTimeout(() => document.getElementById('fc1').focus(), 100);
}

function fdCloseCode() {
    document.getElementById('fdCodeModal').classList.remove('active');
    fdCurrentOrder = null;
}

function fdCodeNext(el, next) {
    if (el.value.length === 1 && next <= 4) document.getElementById('fc' + next).focus();
}

function fdCodeAutoSubmit() {
    if (document.getElementById('fc4').value.length === 1) fdSubmitCode();
}

function fdSubmitCode() {
    const code = ['fc1','fc2','fc3','fc4'].map(id => document.getElementById(id).value).join('');
    if (code.length !== 4) return;

    const btn = document.getElementById('fdConfirmBtn');
    btn.disabled = true; btn.textContent = '...';

    fetch(`/prestataire/food/food-orders/${fdCurrentOrder}/verify-code`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: code })
    })
    .then(async r => {
        const isJson = (r.headers.get('content-type') || '').includes('application/json');
        const payload = isJson ? await r.json() : { success: false, message: 'Réponse invalide' };
        return { ok: r.ok, ...payload };
    })
    .then(data => {
        if (data.success) { fdCloseCode(); location.reload(); }
        else {
            const err = document.getElementById('fdCodeError');
            err.textContent = data.message || 'Code incorrect, réessayez';
            err.style.display = 'block';
            ['fc1','fc2','fc3','fc4'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('fc1').focus();
        }
        btn.disabled = false; btn.textContent = 'Valider';
    })
    .catch(() => {
        document.getElementById('fdCodeError').textContent = 'Erreur réseau';
        document.getElementById('fdCodeError').style.display = 'block';
        btn.disabled = false; btn.textContent = 'Valider';
    });
}

/* ═══════ HISTORY FILTER ═══════ */
function fdFilterHistory() {
    const statusEl = document.getElementById('fdStatusFilter');
    const searchEl = document.getElementById('fdSearchFilter');
    const dateEl = document.getElementById('fdDateFilter');
    const s = (statusEl?.value || '').trim();
    const q = (searchEl?.value || '').trim().replace(/^#/, '');
    const d = (dateEl?.value || '').trim();

    const url = new URL(`{{ route("prestataire.food-orders.dashboard") }}`);
    url.searchParams.set('tab', 'history');
    if (s) url.searchParams.set('status', s);
    if (q) url.searchParams.set('search', q);
    if (/^\d{4}-\d{2}-\d{2}$/.test(d)) url.searchParams.set('date', d);

    window.location.href = url.toString();
}

function fdBindDashboardInteractions() {
    document.getElementById('fdSoundBtn')?.addEventListener('click', fdToggleSound);
    document.getElementById('fdToggleOpenBtn')?.addEventListener('click', fdToggleOpenStatus);
    document.getElementById('fdRefreshBtn')?.addEventListener('click', () => window.location.reload());
    document.getElementById('fdHistApplyBtn')?.addEventListener('click', fdFilterHistory);
    document.getElementById('fdStatusFilter')?.addEventListener('change', fdFilterHistory);
    document.getElementById('fdDateFilter')?.addEventListener('change', fdFilterHistory);
    document.getElementById('fdCodeCancelBtn')?.addEventListener('click', fdCloseCode);
    document.getElementById('fdConfirmBtn')?.addEventListener('click', fdSubmitCode);
    document.getElementById('fdStatModalCloseBtn')?.addEventListener('click', fdCloseStatDetail);

    document.querySelectorAll('.fd-stat[data-stat-detail]').forEach((card) => {
        const key = card.dataset.statDetail;

        card.addEventListener('click', () => fdOpenStatDetail(key));
        card.addEventListener('keydown', (event) => fdStatKeyOpen(event, key));
    });

    document.querySelectorAll('.fd-tab[data-tab]').forEach((tab) => {
        const activate = () => fdSwitchTab(tab.dataset.tab);

        tab.addEventListener('click', activate);
        tab.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            activate();
        });
    });

    document.querySelectorAll('.fd-subtab[data-panel]').forEach((subtab) => {
        const activate = () => fdPanel(Number.parseInt(subtab.dataset.panel || '0', 10));

        subtab.addEventListener('click', activate);
        subtab.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            activate();
        });
    });

    document.querySelectorAll('[data-fd-internal-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            fdFilterInternalBoard(button.dataset.fdInternalFilter || 'all');
        });
    });

    document.querySelectorAll('[data-fd-order-link]').forEach((card) => {
        const navigate = () => {
            const href = card.dataset.href;

            if (href) {
                window.location.href = href;
            }
        };

        card.addEventListener('click', (event) => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            if (target.closest('a, button, input, select, textarea, label, form')) {
                return;
            }

            navigate();
        });

        card.addEventListener('keydown', (event) => {
            if ((event.key !== 'Enter' && event.key !== ' ') || event.target !== card) {
                return;
            }

            event.preventDefault();
            navigate();
        });
    });

    document.querySelectorAll('[data-fd-open-code]').forEach((button) => {
        button.addEventListener('click', () => {
            fdOpenCode(Number.parseInt(button.dataset.fdOpenCode || '0', 10));
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Confirmer cette action ?')) {
                event.preventDefault();
            }
        });
    });

    document.getElementById('fdSearchFilter')?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        fdFilterHistory();
    });

    document.querySelectorAll('[data-fd-code-input]').forEach((input) => {
        input.addEventListener('focus', () => input.select());
        input.addEventListener('input', () => {
            if (input.dataset.next === 'submit') {
                fdCodeAutoSubmit();
                return;
            }

            const next = Number.parseInt(input.dataset.next || '0', 10);
            if (next > 0) {
                fdCodeNext(input, next);
            }
        });
    });
}

fdBindDashboardInteractions();

/* ═══════ KEYBOARD ═══════ */
document.addEventListener('keydown', e => {
    if (document.getElementById('fdStatModal').classList.contains('active') && e.key === 'Escape') {
        fdCloseStatDetail();
        return;
    }

    if (document.getElementById('fdCodeModal').classList.contains('active')) {
        if (e.key === 'Escape') fdCloseCode();
        if (e.key === 'Backspace') {
            const a = document.activeElement;
            if (a.id?.startsWith('fc') && a.value === '') {
                const prev = parseInt(a.id.charAt(2)) - 1;
                if (prev >= 1) document.getElementById('fc' + prev).focus();
            }
        }
    }
});
</script>
@endpush
