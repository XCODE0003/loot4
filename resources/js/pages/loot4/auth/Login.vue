<script setup>
import { Form, Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'

defineProps({
  status: { type: String, default: null },
  canResetPassword: { type: Boolean, default: false },
})
</script>

<template>
  <Head title="Log in — Loot4you" />
  <section class="auth">
    <Container>
      <div class="auth_card">
        <h1 class="auth_title">{{ $t('auth.welcomeBack') }}</h1>
        <p class="auth_sub">{{ $t('auth.loginSub') }}</p>

        <p v-if="status" class="auth_status">{{ status }}</p>

        <Form action="/login" method="post" :reset-on-success="['password']" v-slot="{ errors, processing }" class="auth_form">
          <div class="auth_field">
            <label for="email">{{ $t('auth.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="email" required autofocus placeholder="email@example.com" />
            <p v-if="errors.email" class="auth_error">{{ errors.email }}</p>
          </div>

          <div class="auth_field">
            <div class="auth_field_head">
              <label for="password">{{ $t('auth.password') }}</label>
              <Link v-if="canResetPassword" href="/forgot-password" class="auth_link">{{ $t('auth.forgot') }}</Link>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required :placeholder="$t('auth.password')" />
            <p v-if="errors.password" class="auth_error">{{ errors.password }}</p>
          </div>

          <label class="auth_remember">
            <input name="remember" type="checkbox" />
            <span>{{ $t('auth.remember') }}</span>
          </label>

          <button type="submit" class="auth_btn" :disabled="processing">
            {{ processing ? $t('auth.loggingIn') : $t('auth.login') }}
          </button>
        </Form>

        <p class="auth_alt">
          {{ $t('auth.noAccount') }}
          <Link href="/register" class="auth_link">{{ $t('auth.signup') }}</Link>
        </p>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.auth {
  padding: 80px 0 120px;
  min-height: 60vh;
}
.auth_card {
  max-width: 440px;
  margin: 0 auto;
  padding: 40px;
  border-radius: 22px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.auth_title {
  color: #fff;
  font-size: 28px;
  font-weight: 700;
}
.auth_sub {
  color: rgba(255, 255, 255, 0.55);
  margin-top: 8px;
  margin-bottom: 26px;
}
.auth_status {
  color: #4ade80;
  margin-bottom: 16px;
}
.auth_form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.auth_field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.auth_field_head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.auth_field label {
  color: rgba(255, 255, 255, 0.75);
  font-size: 14px;
}
.auth_field input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 12px;
  background: rgba(15, 168, 84, 0.1);
  border: 1px solid rgba(43, 255, 149, 0.2);
  color: #fff;
}
.auth_field input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}
.auth_error {
  color: #ff6b6b;
  font-size: 13px;
}
.auth_remember {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
  cursor: pointer;
}
.auth_btn {
  margin-top: 6px;
  padding: 16px;
  border-radius: 14px;
  background: linear-gradient(90deg, #0fa854, #2bff95);
  color: #fff;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
}
.auth_btn:disabled {
  opacity: 0.6;
  cursor: default;
}
.auth_alt {
  margin-top: 24px;
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
}
.auth_link {
  color: #2bff95;
}
</style>
