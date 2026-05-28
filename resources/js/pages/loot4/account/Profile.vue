<script setup>
import { Form, Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import AccountShell from '@/loot4/components/account/AccountShell.vue'

defineProps({
  user: { type: Object, required: true },
})
</script>

<template>
  <Head title="Profile — Loot4you" />
  <AccountShell :title="$t('account.profile')">
    <div class="pf_grid">
    <div class="pf_panel">
      <h2 class="pf_panel_title">{{ $t('account.profileInfo') }}</h2>
      <Form action="/account/profile" method="patch" v-slot="{ errors, processing, recentlySuccessful }" class="pf_form">
        <div class="pf_field">
          <label for="name">{{ $t('account.name') }}</label>
          <input id="name" name="name" type="text" :value="user.name" required />
          <p v-if="errors.name" class="pf_error">{{ errors.name }}</p>
        </div>
        <div class="pf_field">
          <label for="email">{{ $t('account.email') }}</label>
          <input id="email" name="email" type="email" :value="user.email" required />
          <p v-if="errors.email" class="pf_error">{{ errors.email }}</p>
        </div>
        <div class="pf_actions">
          <button type="submit" class="pf_btn" :disabled="processing">{{ $t('account.save') }}</button>
          <span v-if="recentlySuccessful" class="pf_ok">{{ $t('account.saved') }}</span>
        </div>
      </Form>
    </div>

    <div class="pf_panel">
      <h2 class="pf_panel_title">{{ $t('account.changePassword') }}</h2>
      <Form
        action="/account/password"
        method="put"
        :reset-on-success="['current_password', 'password', 'password_confirmation']"
        v-slot="{ errors, processing, recentlySuccessful }"
        class="pf_form"
      >
        <div class="pf_field">
          <label for="current_password">{{ $t('account.currentPassword') }}</label>
          <input id="current_password" name="current_password" type="password" autocomplete="current-password" required />
          <p v-if="errors.current_password" class="pf_error">{{ errors.current_password }}</p>
        </div>
        <div class="pf_field">
          <label for="password">{{ $t('account.newPassword') }}</label>
          <input id="password" name="password" type="password" autocomplete="new-password" required />
          <p v-if="errors.password" class="pf_error">{{ errors.password }}</p>
        </div>
        <div class="pf_field">
          <label for="password_confirmation">{{ $t('account.confirmPassword') }}</label>
          <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required />
        </div>
        <div class="pf_actions">
          <button type="submit" class="pf_btn" :disabled="processing">{{ $t('account.updatePassword') }}</button>
          <span v-if="recentlySuccessful" class="pf_ok">{{ $t('account.updated') }}</span>
        </div>
      </Form>
    </div>
    </div>
  </AccountShell>
</template>

<style scoped>
.pf_grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  align-items: start;
}
.pf_panel {
  padding: 24px;
  border-radius: 18px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
@media (max-width: 760px) {
  .pf_grid {
    grid-template-columns: 1fr;
  }
}
.pf_panel_title {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 18px;
}
.pf_form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.pf_field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pf_field label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
}
.pf_field input {
  width: 100%;
  padding: 13px 16px;
  border-radius: 12px;
  background: rgba(15, 168, 84, 0.1);
  border: 1px solid rgba(43, 255, 149, 0.2);
  color: #fff;
}
.pf_error {
  color: #ff6b6b;
  font-size: 13px;
}
.pf_actions {
  display: flex;
  align-items: center;
  gap: 14px;
}
.pf_btn {
  padding: 13px 26px;
  border-radius: 12px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}
.pf_btn:disabled {
  opacity: 0.6;
  cursor: default;
}
.pf_ok {
  color: #4ade80;
  font-size: 14px;
}
</style>
