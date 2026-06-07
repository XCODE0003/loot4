<script setup>
import { computed } from 'vue'
import { Form, Head, Link, usePage } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'
import SocialAuth from '@/loot4/components/auth/SocialAuth.vue'

defineProps({
  status: { type: String, default: null },
  canResetPassword: { type: Boolean, default: false },
})

const page = usePage()
const oauthError = computed(() => page.props.errors?.oauth ?? null)
</script>

<template>
  <Head title="Log in — Loot4you" />
  <section class="auth">
    <Container>
      <div class="auth_card">
        <h1 class="auth_title">{{ $t('auth.welcomeBack') }}</h1>
        <p class="auth_sub">{{ $t('auth.loginSub') }}</p>

        <p v-if="status" class="auth_status">{{ status }}</p>
        <p v-if="oauthError" class="auth_error auth_error--block">{{ oauthError }}</p>

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
            <!-- Checked by default — keeps customers signed in long-term
                 (Fortify issues the remember cookie) instead of dropping
                 them after the session expires. -->
            <input name="remember" type="checkbox" checked />
            <span>{{ $t('auth.remember') }}</span>
          </label>

          <button type="submit" class="auth_btn" :disabled="processing">
            {{ processing ? $t('auth.loggingIn') : $t('auth.login') }}
          </button>
        </Form>

        <SocialAuth />

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
.auth_error--block {
  margin-bottom: 16px;
  padding: 10px 14px;
  border-radius: 10px;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.25);
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
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
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
