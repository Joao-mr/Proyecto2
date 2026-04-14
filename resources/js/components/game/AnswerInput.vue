<template>
  <div class="game-answer-panel">
    <form class="game-answer-panel__form" @submit.prevent="handleSubmit">
      <input
        ref="inputRef"
        v-model="answer"
        class="game-answer-panel__input"
        type="text"
        placeholder="¿Qué hay en la imagen?"
        maxlength="120"
        autocomplete="off"
        :disabled="disabled"
      />
      <button
        type="submit"
        class="game-answer-panel__btn"
        :disabled="disabled || !answer.trim()"
      >
        Responder
        <span aria-hidden="true">›</span>
      </button>
    </form>

    <!-- Feedback visual -->
    <Transition name="feedback-slide">
      <div
        v-if="feedback"
        class="game-feedback"
        :class="feedbackClass"
        role="status"
        aria-live="polite"
      >
        <span>{{ feedbackIcon }}</span>
        {{ feedbackMessage }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  feedback: {
    type: String,
    default: null, // 'correct' | 'wrong' | 'timeout' | null
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

/* Expose focus for parent to call after round change */
defineExpose({ focus: () => inputRef.value?.focus() });
</script>
