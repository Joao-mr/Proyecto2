<template>
  <div class="game-answer-panel">
    <form class="game-answer-panel__form" @submit.prevent="handleSubmit">
      <input
        ref="inputRef"
        v-model="answer"
        class="game-answer-panel__input"
        type="text"
        placeholder="¿Qué hay en la imagen?"
        aria-label="Respuesta de la imagen"
        maxlength="120"
        autocomplete="off"
        enterkeyhint="send"
        :disabled="disabled"
      />
      <button
        type="submit"
        class="game-answer-panel__btn"
        aria-label="Enviar respuesta"
        :disabled="disabled || !answer.trim()"
      >
        <i class="pi pi-send"></i>
        Responder
      </button>
    </form>

    <!-- Feedback visual -->
    <Transition name="feedback-slide">
      <div
        v-if="feedback"
        class="game-feedback alert mt-3 mb-0 d-flex align-items-center gap-2"
        :class="[feedbackClass, feedbackAlertClass]"
        role="status"
        aria-live="polite"
      >
        <span class="fs-5">{{ feedbackIcon }}</span>
        <span>{{ feedbackMessage }}</span>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  feedback: {
    type: String,
    default: null, 
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  correctAnswer: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['submit']);

const answer = ref('');
const inputRef = ref(null);

function handleSubmit() {
  if (!answer.value.trim() || props.disabled) return;
  emit('submit', answer.value.trim());
  answer.value = '';
}

const feedbackClass = computed(() => ({
  'game-feedback--correct': props.feedback === 'correct',
  'game-feedback--wrong':   props.feedback === 'wrong',
  'game-feedback--timeout': props.feedback === 'timeout',
}));

const feedbackAlertClass = computed(() => {
  if (props.feedback === 'correct') return 'alert-success';
  if (props.feedback === 'wrong')   return 'alert-danger';
  if (props.feedback === 'timeout') return 'alert-warning';
  return '';
});

const feedbackIcon = computed(() => {
  if (props.feedback === 'correct') return '✅';
  if (props.feedback === 'wrong')   return '❌';
  if (props.feedback === 'timeout') return '⏱️';
  return '';
});

const feedbackMessage = computed(() => {
  if (props.feedback === 'correct') return '¡Correcto! +50 puntos';
  if (props.feedback === 'wrong')
    return props.correctAnswer
      ? `Incorrecto — era: "${props.correctAnswer}"`
      : 'Incorrecto. Sigue intentando.';
  if (props.feedback === 'timeout') return '¡Tiempo agotado!';
  return '';
});

defineExpose({ focus: () => inputRef.value?.focus() });
</script>
