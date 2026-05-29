<script setup>
// Full-page navigations (not Inertia visits) — OAuth redirects leave the SPA.
</script>

<template>
  <div class="sa">
    <div class="sa_divider"><span>Or continue with</span></div>

    <a href="/auth/discord/redirect" class="sa_btn">
      <svg width="20" height="20" viewBox="0 0 640 640" fill="currentColor" aria-hidden="true">
        <path d="M524.5 133.8C485.6 115.6 445.3 103.1 404 96c-.7-.1-1.4.2-1.8 1-5.5 9.9-10.5 20.2-14.9 30.6-44.6-6.8-89.9-6.8-134.4 0-4.5-10.5-9.5-20.7-15.1-30.6-.4-.7-1.1-1.1-1.8-1-41.3 7.1-81.6 19.6-119.7 37.1-.3.1-.6.4-.8.7C39.1 247.5 18.2 358.6 28.4 468.2c0 .3.2.6.5.8 44.4 32.9 94 58 146.8 74.2.7.2 1.5 0 1.9-.6 11.3-15.4 21.4-31.8 30-48.8.5-1-.1-2.2-1.1-2.6-16.5-6.3-32.2-13.9-47.3-22.8-1.1-.6-1.2-2.2-.2-3 3.2-2.4 6.4-4.9 9.4-7.4.5-.4 1.2-.5 1.8-.2 99.2 45.3 206.6 45.3 304.6 0 .6-.3 1.3-.2 1.8.2 3 2.5 6.1 5 9.4 7.4 1 .8.9 2.4-.2 3-15.1 8.9-30.8 16.5-47.3 22.8-1 .4-1.5 1.6-1 2.6 8.8 17 18.9 33.3 30 48.8.4.6 1.2.8 1.9.6 52.9-16.2 102.6-41.3 147-74.2.3-.2.5-.5.5-.8 12.2-126.7-20.6-236.8-87-341.4-.2-.3-.5-.6-.8-.7zM222.5 401.5c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.2 59.2-52.8 59.2z"/>
      </svg>
      Login with Discord
    </a>

    <a href="/auth/google/redirect" class="sa_btn">
      <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="#4285F4" d="M23.766 12.276c0-.816-.066-1.636-.207-2.438H12.24v4.621h6.482a5.55 5.55 0 0 1-2.4 3.645v3.001h3.867c2.27-2.09 3.577-5.177 3.577-8.829z"/>
        <path fill="#34A853" d="M12.24 24c3.236 0 5.965-1.062 7.954-2.896l-3.867-3.001c-1.076.732-2.465 1.146-4.083 1.146-3.13 0-5.785-2.112-6.737-4.952H1.52v3.091A11.998 11.998 0 0 0 12.24 24z"/>
        <path fill="#FBBC04" d="M5.503 14.297a7.193 7.193 0 0 1 0-4.594V6.612H1.52a11.998 11.998 0 0 0 0 10.776l3.983-3.091z"/>
        <path fill="#EA4335" d="M12.24 4.75c1.71-.026 3.363.612 4.602 1.794l3.426-3.426A11.998 11.998 0 0 0 1.52 6.612l3.983 3.091C6.455 6.862 9.11 4.75 12.24 4.75z"/>
      </svg>
      Login with Google
    </a>
  </div>
</template>

<style scoped>
.sa {
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.sa_divider {
  display: flex;
  align-items: center;
  gap: 14px;
  color: rgba(255, 255, 255, 0.4);
  font-size: 13px;
  margin: 2px 0 8px;
}
.sa_divider::before,
.sa_divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
}
.sa_btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 13px 16px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  transition: background 0.2s, border-color 0.2s;
}
.sa_btn:hover {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.2);
}
</style>
