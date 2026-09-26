@extends('layouts.auth')

@section('title', 'Sign In')

@section('body-class', 'antialiased m-0 p-0 overflow-x-hidden')

@section('styles')
<style>
:root {
  color-scheme: light;
  --ground:#f1f4f0; --surface:#ffffff; --surface-2:#f9fbf8; --sunken:#e8efe7;
  --line:#dce4db; --line-strong:#c3cfc1;
  --ink:#111d16; --ink-2:#4b5b52; --ink-3:#77877d;
  --brand:#0b6b3a; --brand-strong:#07512c; --brand-soft:#e5f1e9;
  --nav-bg:#083b22; --nav-bg-2:#062f1b; --nav-line:rgba(255,255,255,.10);
  --nav-ink:#e6efe8; --nav-ink-dim:#9bb8a6;
  --gold:#8f6b13; --gold-soft:#f7efdb;
  --good:#0f7a44; --good-bg:#e3f0e7;
  --warn:#8f5a00; --warn-bg:#fbeed6;
  --crit:#9d2124; --crit-bg:#fae5e5;
  --info:#1d5aa0; --info-bg:#e4edf8;
  --shadow-1:0 1px 2px rgba(17,29,22,.05);
  --shadow-2:0 4px 16px -4px rgba(17,29,22,.13), 0 1px 3px rgba(17,29,22,.06);
  --shadow-doc:0 12px 40px -12px rgba(17,29,22,.28), 0 2px 6px rgba(17,29,22,.08);
  --r:6px; --r-lg:10px;
  --sans:"Archivo","Helvetica Neue",Arial,sans-serif;
  --serif:"Newsreader",Georgia,"Times New Roman",serif;
  --mono:"IBM Plex Mono",ui-monospace,"SFMono-Regular",Menlo,monospace;
}

@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    color-scheme: dark;
    --ground:#0c1310; --surface:#16201b; --surface-2:#1a251f; --sunken:#101812;
    --line:#26332c; --line-strong:#37483e;
    --ink:#e8f1ea; --ink-2:#a6b8ac; --ink-3:#7b8d82;
    --brand:#3aa96e; --brand-strong:#57c186; --brand-soft:#152e21;
    --nav-bg:#0a1b12; --nav-bg-2:#07140d; --nav-line:rgba(255,255,255,.08);
    --nav-ink:#dde9e1; --nav-ink-dim:#89a696;
    --gold:#d2a53c; --gold-soft:#2a2213;
    --good:#4cba7d; --good-bg:#12291d;
    --warn:#d7a141; --warn-bg:#2a2112;
    --crit:#e37376; --crit-bg:#2c1517;
    --info:#69a4e6; --info-bg:#122233;
    --shadow-1:0 1px 2px rgba(0,0,0,.4);
    --shadow-2:0 4px 16px -4px rgba(0,0,0,.5), 0 1px 3px rgba(0,0,0,.3);
    --shadow-doc:0 12px 40px -12px rgba(0,0,0,.7), 0 2px 6px rgba(0,0,0,.4);
  }
}

/* Reset for signin wrapper */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: var(--sans);
  background: var(--ground);
  color: var(--ink);
}

.icon {
  width: 16px;
  height: 16px;
  flex: none;
  stroke: currentColor;
  fill: none;
  stroke-width: 1.6;
  stroke-linecap: round;
  stroke-linejoin: round;
}

/* ---------- sign-in structure ---------- */
.signin {
  min-height: 100vh;
  display: grid;
  grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
  background: var(--ground);
}

.signin .left {
  background: linear-gradient(155deg, var(--nav-bg) 0%, var(--nav-bg-2) 70%);
  color: var(--nav-ink);
  padding: 46px 44px;
  display: flex;
  flex-direction: column;
  gap: 30px;
  position: relative;
  overflow: hidden;
}

.signin .left:after {
  content: "";
  position: absolute;
  right: -140px;
  bottom: -140px;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.07);
  box-shadow: 0 0 0 60px rgba(255,255,255,.03);
  pointer-events: none;
}

.brandbar {
  display: flex;
  gap: 11px;
  align-items: center;
}

.crest {
  width: 38px;
  height: 38px;
  flex: none;
  border-radius: 50%;
  background: var(--nav-bg-2);
  border: 1.5px solid var(--gold);
  display: grid;
  place-items: center;
  box-shadow: inset 0 0 0 2.5px rgba(255,255,255,.05);
}

.crest svg {
  width: 21px;
  height: 21px;
}

.brandbar .bt {
  font-family: var(--serif);
  font-size: 15.5px;
  font-weight: 600;
  line-height: 1.15;
  color: var(--nav-ink);
}

.brandbar .bs {
  font-size: 11px;
  color: var(--nav-ink-dim);
  margin-top: 2px;
}

.signin .left h2 {
  font-family: var(--serif);
  font-size: 31px;
  font-weight: 600;
  line-height: 1.18;
  letter-spacing: -.015em;
  max-width: 22ch;
  color: var(--nav-ink);
}

.signin .left p {
  font-size: 13.5px;
  color: var(--nav-ink-dim);
  max-width: 44ch;
  line-height: 1.65;
}

.modlist {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 9px 18px;
  font-size: 12.5px;
  margin-top: auto;
  position: relative;
}

.modlist div {
  display: flex;
  gap: 8px;
  align-items: center;
  color: var(--nav-ink);
  font-weight: 500;
}

.modlist .icon {
  width: 14px;
  height: 14px;
  color: var(--gold);
  opacity: .9;
}

.signin .right {
  background: var(--ground);
  display: grid;
  place-items: center;
  padding: 40px 32px;
}

.form {
  width: 100%;
  max-width: 352px;
}

.eyebrow {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .09em;
  text-transform: uppercase;
  color: var(--ink-3);
}

.form h2 {
  font-family: var(--serif);
  font-size: 26px;
  margin-top: 8px;
  letter-spacing: -.015em;
  font-weight: 600;
  color: var(--ink);
  line-height: 1.2;
}

.muted {
  color: var(--ink-2);
}

.form .lb {
  display: block;
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: .05em;
  text-transform: uppercase;
  color: var(--ink-3);
  margin: 16px 0 6px;
}

.form .inp {
  display: flex;
  align-items: center;
  gap: 9px;
  background: var(--surface);
  border: 1px solid var(--line-strong);
  border-radius: var(--r);
  padding: 10px 12px;
  box-shadow: var(--shadow-1);
  transition: border-color .15s ease, box-shadow .15s ease;
}

.form .inp:focus-within {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px var(--brand-soft);
}

.form .inp input {
  border: 0;
  background: none;
  font: inherit;
  color: var(--ink);
  outline: none;
  width: 100%;
  font-size: 13.5px;
}

.form .inp input::placeholder {
  color: var(--ink-3);
  opacity: 0.7;
}

.form .inp .icon {
  color: var(--ink-3);
}

.toggle-eye {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  color: var(--ink-3);
  display: flex;
  align-items: center;
  transition: color .15s ease;
}

.toggle-eye:hover {
  color: var(--brand);
}

.forgot-link {
  font-size: 11px;
  font-weight: 600;
  color: var(--brand);
  text-decoration: none;
  transition: opacity .15s ease;
}

.forgot-link:hover {
  text-decoration: underline;
}

.remember-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 14px;
}

.remember-row input[type="checkbox"] {
  accent-color: var(--brand);
  width: 15px;
  height: 15px;
  cursor: pointer;
  border-radius: 3px;
}

.remember-row label {
  font-size: 12px;
  font-weight: 500;
  color: var(--ink-2);
  cursor: pointer;
  user-select: none;
}

.btn {
  border: 1px solid var(--line-strong);
  background: var(--surface);
  border-radius: var(--r);
  padding: 7px 13px;
  font-size: 12.5px;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  white-space: nowrap;
  font-family: inherit;
  transition: all .15s ease;
  text-decoration: none;
}

.btn:hover {
  border-color: var(--ink-3);
}

.btn.pri {
  background: var(--brand);
  border-color: var(--brand-strong);
  color: #fff;
  box-shadow: var(--shadow-1);
}

.btn.pri:hover {
  background: var(--brand-strong);
}

.form .btn {
  width: 100%;
  justify-content: center;
  margin-top: 22px;
  padding: 11px;
  font-size: 13.5px;
  font-weight: 600;
}

.form .fine {
  font-size: 11.5px;
  color: var(--ink-3);
  margin-top: 16px;
  line-height: 1.6;
  text-align: center;
}

.twofa {
  display: flex;
  gap: 8px;
  align-items: center;
  background: var(--info-bg);
  border: 1px solid color-mix(in srgb, var(--info) 22%, transparent);
  color: var(--info);
  border-radius: var(--r);
  padding: 9px 11px;
  font-size: 11.5px;
  margin-top: 16px;
  line-height: 1.45;
}

.twofa .icon {
  color: var(--info);
}

/* Alert Boxes */
.alert-box {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 10px 12px;
  border-radius: var(--r);
  font-size: 12px;
  margin-bottom: 18px;
  line-height: 1.45;
}

.alert-box.crit {
  background: var(--crit-bg);
  color: var(--crit);
  border: 1px solid color-mix(in srgb, var(--crit) 28%, transparent);
}

.alert-box.good {
  background: var(--good-bg);
  color: var(--good);
  border: 1px solid color-mix(in srgb, var(--good) 26%, transparent);
}

.alert-box.info {
  background: var(--info-bg);
  color: var(--info);
  border: 1px solid color-mix(in srgb, var(--info) 26%, transparent);
}

.alert-box .icon {
  margin-top: 1px;
}

/* Mobile brand inside form */
.mobile-brand {
  display: none;
  align-items: center;
  gap: 10px;
  margin-bottom: 22px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--line);
}

@media (max-width: 900px) {
  .signin {
    grid-template-columns: minmax(0, 1fr);
  }
  .signin .left {
    display: none;
  }
  .signin .right {
    padding: 36px 20px;
  }
  .mobile-brand {
    display: flex;
  }
}
</style>
@endsection

@section('content')
<!-- SVG Symbols from UI/index.html -->
<svg width="0" height="0" style="position:absolute;display:none" aria-hidden="true">
  <defs>
    <symbol id="i-naira" viewBox="0 0 24 24"><path d="M6 20V4l12 16V4"/><path d="M4 10h16M4 15h16"/></symbol>
    <symbol id="i-id" viewBox="0 0 24 24"><rect x="2.5" y="5" width="19" height="14" rx="2.4"/><circle cx="8.5" cy="11" r="2.3"/><path d="M4.8 16.6c.6-1.4 2-2.2 3.7-2.2s3.1.8 3.7 2.2M15 9.5h4M15 13h4"/></symbol>
    <symbol id="i-cash" viewBox="0 0 24 24"><rect x="2.6" y="6" width="18.8" height="12" rx="2.2"/><circle cx="12" cy="12" r="2.8"/><path d="M6 9.4v5.2M18 9.4v5.2"/></symbol>
    <symbol id="i-shop" viewBox="0 0 24 24"><path d="M3 9l1.6-5h14.8L21 9"/><path d="M4.5 9v11h15V9"/><path d="M9.5 20v-6.5h5V20"/></symbol>
    <symbol id="i-receipt" viewBox="0 0 24 24"><path d="M5.2 3.2h13.6v17.6l-2.3-1.7-2.3 1.7-2.3-1.7-2.3 1.7-2.4-1.7z"/><path d="M8.5 8h7M8.5 11.5h7M8.5 15h4"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3.2l8 3v5.9c0 4.9-3.4 8.2-8 8.9-4.6-.7-8-4-8-8.9V6.2z"/><path d="M9 12.2l2.2 2.2L15.2 10"/></symbol>
    <symbol id="i-lock" viewBox="0 0 24 24"><rect x="4.8" y="10.6" width="14.4" height="10.2" rx="2.2"/><path d="M8.2 10.6V8a3.8 3.8 0 0 1 7.6 0v2.6"/></symbol>
    <symbol id="i-key" viewBox="0 0 24 24"><circle cx="8" cy="15.4" r="4"/><path d="M10.9 12.5L19 4.4l1.8 1.8-1.9 1.9 1.9 1.9-2.8 2.8-1.9-1.9"/></symbol>
    <symbol id="i-crest" viewBox="0 0 24 24"><path d="M12 2.6l8.2 3v6c0 5-3.5 8.4-8.2 9.2-4.7-.8-8.2-4.2-8.2-9.2v-6z"/><path d="M12 7.2l1.5 3.1 3.4.5-2.5 2.4.6 3.4-3-1.6-3 1.6.6-3.4-2.5-2.4 3.4-.5z" fill="currentColor" stroke="none"/></symbol>
    <symbol id="i-chev" viewBox="0 0 24 24"><path d="M9.5 5.8l6.2 6.2-6.2 6.2"/></symbol>
    <symbol id="i-warn" viewBox="0 0 24 24"><path d="M12 3.6l8.6 15.2H3.4z"/><path d="M12 9.4v4.2M12 16.4h.02"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="M4.5 12.6l5.4 5.4L19.8 6.4"/></symbol>
    <symbol id="i-eye" viewBox="0 0 24 24"><path d="M2.6 12S6 6.4 12 6.4 21.4 12 21.4 12 18 17.6 12 17.6 2.6 12 2.6 12z"/><circle cx="12" cy="12" r="2.9"/></symbol>
    <symbol id="i-eye-off" viewBox="0 0 24 24"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20"/></symbol>
  </defs>
</svg>

<div class="signin" id="signin">
  <!-- Left Side: Editorial Government Portal Branding -->
  <div class="left">
    <div class="brandbar" style="border:0;padding:0">
      <div class="crest"><svg class="icon" style="width:21px;height:21px;color:var(--gold)"><use href="#i-crest"/></svg></div>
      <div>
        <div class="bt">{{ $system_settings['platform_name'] ?? 'Yola South' }}<br>Local Government</div>
        <div class="bs">Adamawa State &middot; Nigeria</div>
      </div>
    </div>

    <div>
      <h2>One record for every naira, every shop and every member of staff.</h2>
      <p style="margin-top:14px">
        YSLG&#8209;IMRS replaces the parallel ledgers, manual receipt books and standalone payroll files with a single operational database for the Council &mdash; assessment through to reconciliation.
      </p>
    </div>

    <div class="modlist">
      <div><svg class="icon"><use href="#i-naira"/></svg>Centralised revenue</div>
      <div><svg class="icon"><use href="#i-id"/></svg>Employee registry</div>
      <div><svg class="icon"><use href="#i-cash"/></svg>Automated payroll</div>
      <div><svg class="icon"><use href="#i-shop"/></svg>Shops &amp; markets</div>
      <div><svg class="icon"><use href="#i-receipt"/></svg>Verifiable receipts</div>
      <div><svg class="icon"><use href="#i-shield"/></svg>Full audit trail</div>
    </div>
  </div>

  <!-- Right Side: Officer Sign-In Form -->
  <div class="right">
    <div class="form">
      <!-- Mobile branding header -->
      <div class="mobile-brand">
        <div class="crest" style="background:var(--nav-bg);width:36px;height:36px">
          <svg class="icon" style="width:20px;height:20px;color:var(--gold)"><use href="#i-crest"/></svg>
        </div>
        <div>
          <div style="font-family:var(--serif);font-size:14.5px;font-weight:600;color:var(--ink);line-height:1.15">{{ $system_settings['platform_name'] ?? 'Yola South' }} Local Government</div>
          <div style="font-size:10.5px;color:var(--ink-3)">Adamawa State &middot; Nigeria</div>
        </div>
      </div>

      <div class="eyebrow">Restricted &middot; Authorised officers only</div>
      <h2>Sign in to YSLG&#8209;IMRS</h2>
      <p class="muted" style="font-size:13px;margin-top:7px">Use the staff credentials issued by the ICT Unit. Accounts are tied to your department and posting.</p>

      <!-- Feedback / Alerts -->
      <div style="margin-top:18px">
        @if($errors->any())
          <div class="alert-box crit">
            <svg class="icon"><use href="#i-warn"/></svg>
            <div style="flex:1">
              <ul style="margin:0;padding-left:14px;list-style-type:disc">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        @if(session('success'))
          <div class="alert-box good">
            <svg class="icon"><use href="#i-check"/></svg>
            <div>{{ session('success') }}</div>
          </div>
        @endif

        @if(session('info'))
          <div class="alert-box info">
            <svg class="icon"><use href="#i-shield"/></svg>
            <div>{{ session('info') }}</div>
          </div>
        @endif
      </div>

      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <!-- Email / Staff number Input -->
        <label class="lb" for="si-user">Staff number or email</label>
        <div class="inp">
          <svg class="icon"><use href="#i-id"/></svg>
          <input id="si-user" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="YSLG-EMP-000114 or email">
        </div>

        <!-- Password Input -->
        <div style="display:flex;justify-content:space-between;align-items:baseline;margin:16px 0 6px">
          <label class="lb" for="si-pass" style="margin:0">Password</label>
          @if(($system_settings['user_can_forget_password'] ?? '1') == '1')
            <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
          @else
            <span style="font-size:10.5px;color:var(--ink-3);cursor:help" title="Password reset via self-service is disabled. Please contact the ICT unit.">Forgot password? Contact ICT</span>
          @endif
        </div>
        <div class="inp" x-data="{ show: false }">
          <svg class="icon"><use href="#i-lock"/></svg>
          <input id="si-pass" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" style="flex:1">
          <button type="button" @click="show = !show" class="toggle-eye" aria-label="Toggle password visibility">
            <svg class="icon" x-show="!show"><use href="#i-eye"/></svg>
            <svg class="icon" x-show="show" style="display:none"><use href="#i-eye-off"/></svg>
          </button>
        </div>

        <!-- Remember me -->
        <div class="remember-row">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Remember this workstation</label>
        </div>

        <!-- Two-factor authentication security note -->
        <div class="twofa">
          <svg class="icon"><use href="#i-key"/></svg>
          <span>This role requires a one&#8209;time code. A 6&#8209;digit token will be sent to the phone number on your staff record.</span>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn pri">
          Sign in<svg class="icon"><use href="#i-chev"/></svg>
        </button>

        <div class="fine">
          Every sign&#8209;in, approval and cancellation on this platform is written to the audit trail with user, device and IP address.
        </div>
      </form>
    </div>
  </div>
</div>
@endsection