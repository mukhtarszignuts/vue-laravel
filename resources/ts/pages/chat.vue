<script lang="ts" setup>
import type { ChatContact as TypeChatContact } from "@/@fake-db/types";
import { messaging } from "@/firebase/index";
import ChatActiveChatUserProfileSidebarContent from "@/pages/chat/ChatActiveChatUserProfileSidebarContent.vue";
import ChatLeftSidebarContent from "@/pages/chat/ChatLeftSidebarContent.vue";
import ChatLog from "@/pages/chat/ChatLog.vue";
import ChatUserProfileSidebarContent from "@/pages/chat/ChatUserProfileSidebarContent.vue";
import { useChat } from "@/pages/chat/useChat";
import { useChatStore } from "@/pages/chat/useChatStore";
import axios from "@/plugins/axios";
import vuetifyInitialThemes from "@/plugins/vuetify/theme";
import { useResponsiveLeftSidebar } from "@core/composable/useResponsiveSidebar";
import { avatarText } from "@core/utils/formatters";
import { getToken } from "firebase/messaging";
import { PerfectScrollbar } from "vue3-perfect-scrollbar";
import { useDisplay, useTheme } from "vuetify";

// composables
const vuetifyDisplays = useDisplay();
const store = useChatStore();
const { isLeftSidebarOpen } = useResponsiveLeftSidebar(
  vuetifyDisplays.smAndDown
);
const { resolveAvatarBadgeVariant } = useChat();

// Perfect scrollbar
const chatLogPS = ref();

const scrollToBottomInChatLog = () => {
  const scrollEl = chatLogPS.value.$el || chatLogPS.value;

  scrollEl.scrollTop = scrollEl.scrollHeight;
};

// Search query
const q = ref("");

const token = ref<any>(null);
const error = ref<any>(null);

watch(q, (val) => store.fetchChatsAndContacts(val), { immediate: true });

// Open Sidebar in smAndDown when "start conversation" is clicked
const startConversation = () => {
  if (vuetifyDisplays.mdAndUp.value) return;
  isLeftSidebarOpen.value = true;
};

// Chat message
const msg = ref("");

const sendMessage = async () => {
  if (!msg.value) return;
  console.log("click", store.activeChat);

  await store.sendMsg(msg.value);

  // Reset message input
  msg.value = "";

  // Scroll to bottom
  nextTick(() => {
    scrollToBottomInChatLog();
  });
};

const openChatOfContact = async (userId: TypeChatContact["id"]) => {
  await store.getChat(userId);

  // Reset message input
  msg.value = "";

  // Set unseenMsgs to 0
  const contact = store.chatsContacts.find((c) => c.id === userId);

  // if (contact) contact.chat.unseenMsgs = 0;
  // calling api for msg seen
  if (contact) contact.chat.unseenMsgs = 0;

  // if smAndDown =>  Close Chat & Contacts left sidebar
  if (vuetifyDisplays.smAndDown.value) isLeftSidebarOpen.value = false;

  // Scroll to bottom
  nextTick(() => {
    scrollToBottomInChatLog();
  });
};

// User profile sidebar
const isUserProfileSidebarOpen = ref(false);

// Active chat user profile sidebar
const isActiveChatUserProfileSidebarOpen = ref(false);

// file input
const refInputEl = ref<HTMLElement>();

const moreList = [
  { title: "View Contact", value: "View Contact" },
  { title: "Mute Notifications", value: "Mute Notifications" },
  { title: "Block Contact", value: "Block Contact" },
  { title: "Clear Chat", value: "Clear Chat" },
  { title: "Report", value: "Report" },
];

const { name } = useTheme();

const chatContentContainerBg = computed(() => {
  let color = "transparent";

  if (vuetifyInitialThemes)
    color = vuetifyInitialThemes.themes?.[name.value].colors
      ?.background as string;

  return color;
});

const handleMoreAction = (item: any) => {
  console.log("item", item);
  if (item.value == "Clear Chat") {
    console.log("current chat account", store.activeChat);
    chatClear(store.activeChat?.chat?.id);
  }
};

const chatClear = async (id: any) => {
  const data = await store.chatClear(id);
};

const getDeviceToken = async () => {
      try {
        // Request notification permission
        const permission = await Notification.requestPermission();
        if (permission === "granted") {
          console.log("Notification permission granted.");

          // Get FCM token
          const vapidKey = import.meta.env.VITE_VAPID_KEY;
          const currentToken = await getToken(messaging, { vapidKey });

          if (currentToken) {
            console.log("currentToken: ", currentToken);
            try {
              // Send token to server
              const result = await axios.post("/user/token-save", { token: currentToken });
              console.log("Token saved successfully.", result);
              token.value = currentToken;
            } catch (err) {
              console.error("Error saving token:", err);
              error.value = "Error saving token";
            }
          } else {
            console.log("No registration token available. Request permission to generate one.");
          }
        } else {
          console.log("Notification permission denied.");
        }
      } catch (err) {
        console.error("An error occurred while retrieving token:", err);
        error.value = "An error occurred while retrieving token";
      }
    };

onMounted(async () => {
  console.log("load chat");
  getDeviceToken();
});
</script>

<template>
  <VLayout class="chat-app-layout">
    <!-- 👉 user profile sidebar -->
    <VNavigationDrawer
      v-model="isUserProfileSidebarOpen"
      temporary
      touchless
      absolute
      class="user-profile-sidebar"
      location="start"
      width="370"
    >
      <ChatUserProfileSidebarContent
        @close="isUserProfileSidebarOpen = false"
      />
    </VNavigationDrawer>

    <!-- 👉 Active Chat sidebar -->
    <VNavigationDrawer
      v-model="isActiveChatUserProfileSidebarOpen"
      width="374"
      absolute
      temporary
      location="end"
      touchless
      class="active-chat-user-profile-sidebar"
    >
      <ChatActiveChatUserProfileSidebarContent
        @close="isActiveChatUserProfileSidebarOpen = false"
      />
    </VNavigationDrawer>

    <!-- 👉 Left sidebar   -->
    <VNavigationDrawer
      v-model="isLeftSidebarOpen"
      absolute
      touchless
      location="start"
      width="370"
      :temporary="$vuetify.display.smAndDown"
      class="chat-list-sidebar"
      :permanent="$vuetify.display.mdAndUp"
    >
      <ChatLeftSidebarContent
        v-model:isDrawerOpen="isLeftSidebarOpen"
        v-model:search="q"
        @open-chat-of-contact="openChatOfContact"
        @show-user-profile="isUserProfileSidebarOpen = true"
        @close="isLeftSidebarOpen = false"
      />
    </VNavigationDrawer>

    <!-- 👉 Chat content -->
    <VMain class="chat-content-container">
      <!-- 👉 Right content: Active Chat -->
      <div v-if="store.activeChat" class="d-flex flex-column h-100">
        <!-- 👉 Active chat header -->
        <div
          class="active-chat-header d-flex align-center text-medium-emphasis bg-surface"
        >
          <!-- Sidebar toggler -->
          <IconBtn class="d-md-none me-3" @click="isLeftSidebarOpen = true">
            <VIcon icon="tabler-menu-2" />
          </IconBtn>

          <!-- avatar -->
          <div
            class="d-flex align-center cursor-pointer"
            @click="isActiveChatUserProfileSidebarOpen = true"
          >
            <VBadge
              dot
              location="bottom right"
              offset-x="3"
              offset-y="0"
              :color="
                resolveAvatarBadgeVariant(store.activeChat.contact.status)
              "
              bordered
            >
              <VAvatar
                size="38"
                :variant="
                  !store.activeChat.contact.avatar ? 'tonal' : undefined
                "
                :color="
                  !store.activeChat.contact.avatar
                    ? resolveAvatarBadgeVariant(store.activeChat.contact.status)
                    : undefined
                "
                class="cursor-pointer"
              >
                <VImg
                  v-if="store.activeChat.contact.avatar"
                  :src="store.activeChat.contact.avatar"
                  :alt="store.activeChat.contact.fullName"
                />
                <span v-else>{{
                  avatarText(store.activeChat.contact.fullName)
                }}</span>
              </VAvatar>
            </VBadge>

            <div class="flex-grow-1 ms-4 overflow-hidden">
              <p class="text-h6 mb-0">
                {{ store.activeChat.contact.fullName }}
              </p>
              <p class="text-truncate mb-0 text-disabled">
                {{ store.activeChat.contact.role }}
              </p>
            </div>
          </div>

          <VSpacer />

          <!-- Header right content -->
          <div class="d-sm-flex align-center d-none">
            <IconBtn>
              <VIcon icon="tabler-phone-call" />
            </IconBtn>
            <IconBtn>
              <VIcon icon="tabler-video" />
            </IconBtn>
            <IconBtn>
              <VIcon icon="tabler-search" />
            </IconBtn>
            <!-- <IconBtn>
             <v-btn color="success" @click="getDeviceToken">Allow</v-btn>
            </IconBtn> -->
          </div>

          <MoreBtn
            :menu-list="moreList"
            density="comfortable"
            color="undefined"
            @action="handleMoreAction"
          />
        </div>

        <VDivider />

        <!-- Chat log -->
        <PerfectScrollbar
          ref="chatLogPS"
          tag="ul"
          :options="{ wheelPropagation: false }"
          class="flex-grow-1"
        >
          <ChatLog />
        </PerfectScrollbar>

        <!-- Message form -->
        <VForm
          class="chat-log-message-form mb-5 mx-5"
          @submit.prevent="sendMessage"
        >
          <VTextField
            :key="store.activeChat?.contact.id"
            v-model="msg"
            variant="solo"
            class="chat-message-input"
            placeholder="Type your message..."
            density="default"
            autofocus
          >
            <template #append-inner>
              <IconBtn>
                <VIcon icon="tabler-microphone" />
              </IconBtn>

              <IconBtn class="me-2" @click="refInputEl?.click()">
                <VIcon icon="tabler-photo" />
              </IconBtn>

              <VBtn @click="sendMessage"> Send </VBtn>
            </template>
          </VTextField>

          <input
            ref="refInputEl"
            type="file"
            name="file"
            accept=".jpeg,.png,.jpg,GIF"
            hidden
          />
        </VForm>
      </div>

      <!-- 👉 Start conversation -->
      <div v-else class="d-flex h-100 align-center justify-center flex-column">
        <VAvatar size="109" class="elevation-3 mb-6 bg-surface">
          <VIcon
            size="50"
            class="rounded-0 text-high-emphasis"
            icon="tabler-message"
          />
        </VAvatar>
        <p
          class="mb-0 px-6 py-1 font-weight-medium text-lg elevation-3 rounded-xl text-high-emphasis bg-surface"
          :class="[{ 'cursor-pointer': $vuetify.display.smAndDown }]"
          @click="startConversation"
        >
          Start Conversation
        </p>
      </div>
    </VMain>
  </VLayout>
</template>

<route lang="yaml">
meta:
  layoutWrapperClasses: layout-content-height-fixed
</route>

<style lang="scss">
@use "@styles/variables/_vuetify.scss";
@use "@core-scss/base/_mixins.scss";
@use "@layouts/styles/mixins" as layoutsMixins;

// Variables
$chat-app-header-height: 62px;

// Placeholders
%chat-header {
  display: flex;
  align-items: center;
  min-block-size: $chat-app-header-height;
  padding-inline: 1rem;
}

.chat-app-layout {
  border-radius: vuetify.$card-border-radius;

  @include mixins.elevation(vuetify.$card-elevation);

  $sel-chat-app-layout: &;

  @at-root {
    .skin--bordered {
      @include mixins.bordered-skin($sel-chat-app-layout);
    }
  }

  .active-chat-user-profile-sidebar,
  .user-profile-sidebar {
    .v-navigation-drawer__content {
      display: flex;
      flex-direction: column;
    }
  }

  .chat-list-header,
  .active-chat-header {
    @extend %chat-header;
  }

  .chat-list-search {
    .v-field__outline__start {
      flex-basis: 20px !important;
      border-radius: 28px 0 0 28px !important;
    }

    .v-field__outline__end {
      border-radius: 0 28px 28px 0 !important;
    }

    @include layoutsMixins.rtl {
      .v-field__outline__start {
        flex-basis: 20px !important;
        border-radius: 0 28px 28px 0 !important;
      }

      .v-field__outline__end {
        border-radius: 28px 0 0 28px !important;
      }
    }
  }

  .chat-list-sidebar {
    .v-navigation-drawer__content {
      display: flex;
      flex-direction: column;
    }
  }
}

.chat-content-container {
  /* stylelint-disable-next-line value-keyword-case */
  background-color: v-bind(chatContentContainerBg);

  // Adjust the padding so text field height stays 48px
  .chat-message-input {
    .v-field__append-inner {
      align-items: center;
      padding-block-start: 0;
    }

    .v-field--appended {
      padding-inline-end: 9px;
    }
  }
}

.chat-user-profile-badge {
  .v-badge__badge {
    /* stylelint-disable liberty/use-logical-spec */
    min-width: 12px !important;
    height: 0.75rem;
    /* stylelint-enable liberty/use-logical-spec */
  }
}
</style>
