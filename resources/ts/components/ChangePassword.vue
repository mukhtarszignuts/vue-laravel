<script setup lang="ts">
import { confirmedValidator, newPasswordVal, passwordValidator, requiredValidator } from '@/@core/utils/validators'
import axios from '@/plugins/axios'
import { ref } from 'vue'
import { toast } from 'vue3-toastify'
import { VForm } from 'vuetify/components/VForm'

// Data
const isLoading = ref<boolean>(false)

const isNewPasswordVisible = ref<boolean>(false)
const isConfirmPasswordVisible = ref<boolean>(false)
const isCurrentPasswordVisible = ref<boolean>(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const refFormPassword = ref<VForm>()

const changePassword = async () => {
  refFormPassword.value?.validate().then(async ({ valid }: any) => {
    if (valid) {
      try {
        isLoading.value = true

        const input = {
          current_password: currentPassword.value,
          password: newPassword.value,
          confirm_password: confirmPassword.value,
        }

        const { data } = await axios.post('/change-password', input)
        if (data) {
          console.log('')
          isLoading.value = false
          toast.success(data.message)
          refFormPassword.value?.reset()
            //   router.push('/')
        }
      }
      catch (e) {
        console.log('error', e)
        isLoading.value = false
        toast.error(e?.response?.data?.message)
      }
    }
  })
}

</script>
<template>
    <VRow>
        <VCol cols="12">
            <!-- 👉 Change password -->
            <VCard title="Change Password">
                <VCardText>
                    <VAlert variant="tonal" color="warning" class="mb-4">
                        <VAlertTitle class="mb-2">
                            Ensure that these requirements are met
                        </VAlertTitle>
                        <span>Minimum 8 characters long, uppercase & symbol</span>
                    </VAlert>

                    <div>
                        <VForm ref="refFormPassword">
                            <VCardText class="pt-0">
                                <!-- 👉 Current Password -->
                                <VRow>
                                    <VCol cols="12" md="4">
                                        <!-- 👉 current password -->
                                        <AppTextField v-model="currentPassword"
                                            :type="isCurrentPasswordVisible ? 'text' : 'password'"
                                            :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                            label="Current Password*"
                                            :rules="[requiredValidator(currentPassword, 'Current Password'),passwordValidator]"
                                            @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible" />
                                    </VCol>
                                    <!-- 👉 New Password -->
                                    <VCol cols="12" md="4">
                                        <!-- 👉 new password -->
                                        <AppTextField v-model="newPassword"
                                            :type="isNewPasswordVisible ? 'text' : 'password'"
                                            :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                            label="New Password*"
                                            :rules="[requiredValidator(newPassword, 'Password'),passwordValidator,newPasswordVal(newPassword, currentPassword)]"
                                            @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible" />
                                    </VCol>
                                    <VCol cols="12" md="4">
                                        <!-- 👉 confirm password -->
                                        <AppTextField v-model="confirmPassword"
                                            :type="isConfirmPasswordVisible ? 'text' : 'password'"
                                            :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                            label="Confirm New Password*"
                                            :rules="[requiredValidator(confirmPassword, 'Confirm new password'), confirmedValidator(confirmPassword, newPassword)]"
                                            @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible" />
                                    </VCol>
                                </VRow>

                            </VCardText>

                            <!-- 👉 Action Buttons -->
                            <VCardText class="d-flex flex-wrap gap-4 ">
                                <VBtn v-if="isLoading" type="submit" loading="'white'">
                                    Save changes
                                </VBtn>
                                <VBtn v-else @click="changePassword">
                                    Save changes
                                </VBtn>
                                <VBtn type="reset" color="secondary" variant="tonal">
                                    Reset
                                </VBtn>
                            </VCardText>
                        </VForm>
                    </div>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>