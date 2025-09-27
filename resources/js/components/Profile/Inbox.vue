<template>
  <div class="common-layout">
    <el-container class="full-height">
      <el-main class="main-center">
        <div class="dashboard-container">
          <Menu />
          <template v-if="user_status !== 'active' || plan_type === 'free_trial'">
              <Message :page="'inbox'" :user_status="user_status" :user_type="user_type" :plan_type="plan_type" />
          </template>
          <template v-else>
            <div class="inbox-container">
              <!-- Chat List Sidebar (30%) -->
              <div class="chat-list-sidebar">
                <div class="sidebar-header">
                  <h3>Messages</h3>
                  <el-button 
                    size="small" 
                    @click="refreshConversations" 
                    :loading="loadingConversations"
                    circle
                  >
                    <el-icon><Refresh /></el-icon>
                  </el-button>
                </div>
                
                <div class="search-container">
                  <el-input
                    v-model="searchQuery"
                    placeholder="Search conversations..."
                    size="small"
                    clearable
                  >
                    <template #prefix>
                      <el-icon><Search /></el-icon>
                    </template>
                  </el-input>
                </div>

                <!-- Conversations List -->
                <div class="conversations-list">
                  <div 
                    v-for="conversation in filteredConversations" 
                    :key="`${conversation.partner_id}-${conversation.application_id}`"
                    :class="['conversation-item', { 'active': isActiveConversation(conversation) }]"
                    @click="selectConversation(conversation)"
                  >
                    <div class="conversation-avatar">
                      <el-avatar :size="40">
                        {{ getInitials(conversation.partner_name) }}
                      </el-avatar>
                    </div>
                    
                    <div class="conversation-details">
                      <div class="conversation-header">
                        <h4>{{ conversation.partner_name }}</h4>
                        <span class="time">{{ formatTime(conversation.last_message_time) }}</span>
                      </div>
                      <div class="conversation-preview">
                        <p class="job-title">{{ conversation.job_title }}</p>
                        <p class="last-message">{{ truncateMessage(conversation.last_message) }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Empty State -->
                  <div v-if="conversations.length === 0 && !loadingConversations" class="empty-conversations">
                    <el-empty description="No conversations yet"></el-empty>
                  </div>

                  <!-- Loading State -->
                  <div v-if="loadingConversations" class="loading-conversations">
                    <el-skeleton :rows="5" animated />
                  </div>
                </div>
              </div>

              <!-- Chat Area (70%) -->
              <div class="chat-area">
                <!-- Chat Header -->
                <div v-if="selectedConversation" class="chat-header">
                  <div class="chat-user-info">
                    <el-avatar :size="35">
                      {{ getInitials(selectedConversation.partner_name) }}
                    </el-avatar>
                    <div class="user-details">
                      <h4>{{ selectedConversation.partner_name }}</h4>
                      <p>{{ selectedConversation.job_title }}</p>
                    </div>
                  </div>
                  <div class="chat-actions">
                    <el-button size="small" @click="refreshMessages" :loading="loadingMessages">
                      <el-icon><Refresh /></el-icon>
                    </el-button>
                  </div>
                </div>

                <!-- Messages Area -->
                <div v-if="selectedConversation" class="messages-container" ref="messagesContainer">
                  <div class="messages-list">
                    <div 
                      v-for="message in messages" 
                      :key="message.id"
                      :class="['message-bubble', message.is_sender ? 'sent' : 'received']"
                    >
                      <div class="message-content">
                        {{ message.message }}
                      </div>
                      <div class="message-time">
                        {{ formatMessageTime(message.created_at) }}
                      </div>
                    </div>

                    <!-- Loading messages -->
                    <div v-if="loadingMessages" class="loading-messages">
                      <el-skeleton :rows="3" animated />
                    </div>

                    <!-- No messages -->
                    <div v-if="messages.length === 0 && !loadingMessages" class="no-messages">
                      <el-empty description="No messages yet. Start the conversation!" />
                    </div>
                  </div>
                </div>

                <!-- Message Input -->
                <div v-if="selectedConversation" class="message-input-container">
                  <div class="message-input">
                    <el-input
                      v-model="newMessage"
                      type="textarea"
                      :rows="2"
                      placeholder="Type your message..."
                      @keyup.enter.ctrl="sendMessage"
                      @keyup.enter.prevent="sendMessage"
                      maxlength="1000"
                      show-word-limit
                      resize="none"
                    />
                    <el-button 
                      type="primary" 
                      @click="sendMessage"
                      :loading="sendingMessage"
                      :disabled="!newMessage.trim()"
                      class="send-button"
                    >
                      <el-icon><Position /></el-icon>
                    </el-button>
                  </div>
                </div>

                <!-- Empty Chat State -->
                <div v-else class="empty-chat">
                  <el-empty description="Select a conversation to start chatting"></el-empty>
                </div>
              </div>
            </div>
          </template>
        </div>
      </el-main>
    </el-container>
  </div>
</template>

<script>
import { onMounted, ref, computed, nextTick, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import Menu from './Menu.vue';
import { loader } from '../../Composable/Loader';
import AlertMessage from '../../Composable/AlertMessage';
import { useAppHelper } from '../../Composable/appHelper';
import { Search, Refresh, Position } from '@element-plus/icons-vue';
import Message from '../Messages/Message.vue';

export default {
  name: 'Inbox',
  components: {
    Menu,
    Message,
    Search,
    Refresh,
    Position,
  },
  setup() {
    const router = useRouter();
    const { error, success } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const { get, post } = useAppHelper();
    const app_vars = window.adjuster_forge_app_vars;
    const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
    const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
    const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;
    // Data
    const conversations = ref([]);
    const messages = ref([]);
    const selectedConversation = ref(null);
    const currentUserId = ref(app_vars.user_data?.current_user_id || null);
    const searchQuery = ref('');
    const newMessage = ref('');
    const refreshInterval = ref(null);

    // Loading states
    const loadingConversations = ref(false);
    const loadingMessages = ref(false);
    const sendingMessage = ref(false);

    // Refs
    const messagesContainer = ref(null);

    // Computed
    const filteredConversations = computed(() => {
      if (!searchQuery.value) return conversations.value;
      return conversations.value.filter(conv => 
        conv.partner_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        conv.job_title.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    // Methods
    const fetchConversations = async () => {
      loadingConversations.value = true;
      try {
        const response = await get('get-user-conversations');
        if (response && response.status === 'success') {
          conversations.value = response.data || [];
        } else {
          error(response?.message || 'Failed to load conversations');
        }
      } catch (err) {
        error(err.message);
      } finally {
        loadingConversations.value = false;
      }
    };

    const selectConversation = async (conversation) => {
      selectedConversation.value = conversation;
      await loadMessages(conversation.partner_id);
      startAutoRefresh();
    };

    const startAutoRefresh = () => {
      // Clear existing interval if any
      if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
      }
      
      // Start new interval for auto-refresh every 30 seconds
      refreshInterval.value = setInterval(() => {
        if (selectedConversation.value) {
          loadMessages(selectedConversation.value.partner_id);
        }
      }, 30000); // 30 seconds
    };

    const stopAutoRefresh = () => {
      if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
      }
    };

    const loadMessages = async (partnerId) => {
      loadingMessages.value = true;
      try {
        const response = await get(`get-conversation/${partnerId}`);
        if (response && response.status === 'success') {
          messages.value = response.data || [];
          await nextTick();
          scrollToBottom();
        } else {
          error(response?.message || 'Failed to load messages');
        }
      } catch (err) {
        error(err.message);
      } finally {
        loadingMessages.value = false;
      }
    };

    const sendMessage = async () => {
      if (!newMessage.value.trim() || !selectedConversation.value) return;

      sendingMessage.value = true;
      try {
        const response = await post('send-message', {
          recipient_id: selectedConversation.value.partner_id,
          message: newMessage.value.trim()
        });

        if (response && response.status === 'success') {
          // Add message to local array
          messages.value = response.data || [...messages.value, {
            message: newMessage.value.trim(),
            sender_id: currentUserId.value,
            recipient_id: selectedConversation.value.partner_id,
            created_at: new Date().toISOString()
          }];
          
          // Update conversation list
          updateConversationLastMessage();
          
          newMessage.value = '';
          await nextTick();
          scrollToBottom();
        } else {
          error(response?.message || 'Failed to send message');
        }
      } catch (err) {
        error(err.message);
      } finally {
        sendingMessage.value = false;
      }
    };

    const updateConversationLastMessage = () => {
      if (!selectedConversation.value) return;
      
      const conv = conversations.value.find(c => 
        c.partner_id === selectedConversation.value.partner_id && 
        c.application_id === selectedConversation.value.application_id
      );
      
      if (conv) {
        conv.last_message = newMessage.value.trim();
        conv.last_message_time = new Date().toISOString();
        
        // Move to top of list
        conversations.value = [conv, ...conversations.value.filter(c => c !== conv)];
      }
    };

    const refreshConversations = () => {
      fetchConversations();
    };

    const refreshMessages = () => {
      if (selectedConversation.value) {
        loadMessages(selectedConversation.value.application_id);
      }
    };

    const scrollToBottom = () => {
      if (messagesContainer.value) {
        const container = messagesContainer.value;
        container.scrollTop = container.scrollHeight;
      }
    };

    const isActiveConversation = (conversation) => {
      return selectedConversation.value && 
             selectedConversation.value.partner_id === conversation.partner_id &&
             selectedConversation.value.application_id === conversation.application_id;
    };

    const getInitials = (name) => {
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    const truncateMessage = (message) => {
      return message.length > 50 ? message.substring(0, 50) + '...' : message;
    };

    const formatTime = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diffInHours = (now - date) / (1000 * 60 * 60);

      if (diffInHours < 24) {
        return date.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit'
        });
      } else {
        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        });
      }
    };

    const formatMessageTime = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    // Watch for selectedConversation changes
    watch(selectedConversation, (newConversation, oldConversation) => {
      if (newConversation) {
        startAutoRefresh();
      } else {
        stopAutoRefresh();
      }
    });

    // Cleanup on component unmount
    onUnmounted(() => {
      stopAutoRefresh();
    });

    // Lifecycle
    onMounted(async () => {
      startLoading();
      try {
        await fetchConversations();
      } finally {
        stopLoading();
      }
    });

    return {
      conversations,
      filteredConversations,
      messages,
      selectedConversation,
      currentUserId,
      searchQuery,
      newMessage,
      loadingConversations,
      loadingMessages,
      sendingMessage,
      messagesContainer,
      selectConversation,
      sendMessage,
      refreshConversations,
      refreshMessages,
      isActiveConversation,
      getInitials,
      truncateMessage,
      formatTime,
      formatMessageTime,
      user_status,
      user_type,
      plan_type,
    };
  },
};
</script>

<style scoped>
.dashboard-container {
  padding: 20px 0;
}

.inbox-container {
  display: flex;
  height: 80vh;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  overflow: hidden;
}

/* Chat List Sidebar */
.chat-list-sidebar {
  width: 30%;
  border-right: 1px solid #e4e7ed;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e4e7ed;
  background: white;
}

.sidebar-header h3 {
  margin: 0;
  color: #303133;
}

.search-container {
  padding: 15px;
  background: white;
  border-bottom: 1px solid #e4e7ed;
}

.conversations-list {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  padding: 15px;
  border-bottom: 1px solid #e4e7ed;
  cursor: pointer;
  transition: background-color 0.2s;
  background: white;
}

.conversation-item:hover {
  background: #f5f7fa;
}

.conversation-item.active {
  background: #e6f7ff;
  border-right: 3px solid #409eff;
}

.conversation-avatar {
  margin-right: 12px;
}

.conversation-details {
  flex: 1;
  min-width: 0;
}

.conversation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.conversation-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #303133;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.conversation-header .time {
  font-size: 12px;
  color: #909399;
  white-space: nowrap;
}

.conversation-preview .job-title {
  font-size: 12px;
  color: #409eff;
  margin: 0 0 2px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.conversation-preview .last-message {
  font-size: 13px;
  color: #606266;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.empty-conversations,
.loading-conversations {
  padding: 40px 20px;
}

/* Chat Area */
.chat-area {
  width: 70%;
  display: flex;
  flex-direction: column;
  background: white;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e4e7ed;
  background: #fafafa;
}

.chat-user-info {
  display: flex;
  align-items: center;
}

.user-details {
  margin-left: 12px;
}

.user-details h4 {
  margin: 0 0 4px 0;
  font-size: 16px;
  color: #303133;
}

.user-details p {
  margin: 0;
  font-size: 13px;
  color: #909399;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.message-bubble {
  display: flex;
  flex-direction: column;
  max-width: 70%;
}

.message-bubble.sent {
  align-self: flex-end;
}

.message-bubble.received {
  align-self: flex-start;
}

.message-content {
  padding: 12px 16px;
  border-radius: 18px;
  word-wrap: break-word;
  line-height: 1.4;
}

.message-bubble.sent .message-content {
  background: #409eff;
  color: white;
  border-bottom-right-radius: 4px;
}

.message-bubble.received .message-content {
  background: #f5f7fa;
  color: #303133;
  border-bottom-left-radius: 4px;
}

.message-time {
  font-size: 11px;
  color: #909399;
  margin-top: 4px;
  padding: 0 8px;
}

.message-bubble.sent .message-time {
  text-align: right;
}

.message-bubble.received .message-time {
  text-align: left;
}

.loading-messages {
  padding: 20px 0;
}

.message-input-container {
  padding: 20px;
  border-top: 1px solid #e4e7ed;
  background: #fafafa;
}

.message-input {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.message-input .el-textarea {
  flex: 1;
}

.send-button {
  height: 40px;
  width: 40px;
  border-radius: 50%;
  padding: 0;
}

.empty-chat {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-chat-actions {
  margin-top: 20px;
}

/* Inactive Account Box */
.inactive-account-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 350px;
  background: #f8fafc;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(60,120,200,0.08);
  padding: 40px 20px;
  margin: 40px auto;
  max-width: 500px;
}

.inactive-message {
  text-align: center;
  margin-top: 10px;
}

.inactive-message h2 {
  font-size: 1.5rem;
  color: #409eff;
  margin-bottom: 8px;
}

.inactive-message p {
  color: #606266;
  font-size: 1.08em;
  margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .inbox-container {
    height: 70vh;
  }
  
  .chat-list-sidebar {
    width: 35%;
  }
  
  .chat-area {
    width: 65%;
  }
  
  .conversation-header h4 {
    font-size: 13px;
  }
  
  .message-bubble {
    max-width: 85%;
  }
}

@media (max-width: 576px) {
  .chat-list-sidebar {
    width: 100%;
  }
  
  .chat-area {
    display: none;
  }
  
  .chat-area.mobile-active {
    display: flex;
    width: 100%;
  }
  
  .chat-list-sidebar.mobile-hidden {
    display: none;
  }
}
</style>
