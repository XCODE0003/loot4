<script setup>
import { Form, Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'

defineProps({
  passwordRules: { type: String, default: null },
})
</script>

<template>
  <Head title="Sign up — Loot4you" />
  <section class="auth">
    <Container>
      <div class="auth_card">
        <h1 class="auth_title">{{ $t('auth.createAccount') }}</h1>
        <p class="auth_sub">{{ $t('auth.registerSub') }}</p>

        <Form
          action="/register"
          method="post"
          :reset-on-success="['password', 'password_confirmation']"
          v-slot="{ errors, processing }"
          class="auth_form"
        >
          <div class="auth_field">
            <label for="name">{{ $t('auth.name') }}</label>
            <input id="name" name="name" type="text" autocomplete="name" required autofocus :placeholder="$t('auth.name')" />
            <p v-if="errors.name" class="auth_error">{{ errors.name }}</p>
          </div>

          <div class="auth_field">
            <label for="email">{{ $t('auth.email') }}</label>
            <input id="email" name="email" type="email" autocomplete="email" required placeholder="email@example.com" />
            <p v-if="errors.email" class="auth_error">{{ errors.email }}</p>
          </div>

          <div class="auth_field">
            <label for="password">{{ $t('auth.password') }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required :placeholder="$t('auth.password')" />
            <p v-if="errors.password" class="auth_error">{{ errors.password }}</p>
          </div>

          <div class="auth_field">
            <label for="password_confirmation">{{ $t('auth.confirm') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required :placeholder="$t('auth.confirm')" />
            <p v-if="errors.password_confirmation" class="auth_error">{{ errors.password_confirmation }}</p>
          </div>

          <button type="submit" class="auth_btn" :disabled="processing">
            {{ processing ? $t('auth.creating') : $t('auth.createBtn') }}
          </button>
        </Form>

        <p class="auth_alt">
          {{ $t('auth.haveAccount') }}
          <Link href="/login" class="auth_link">{{ $t('auth.login') }}</Link>
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
