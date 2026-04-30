import { computed, nextTick, onUnmounted, ref, unref } from 'vue'

const DEFAULT_TOTAL_TIME = 30
const DEFAULT_SCORE_PER_CORRECT = 50
const DEFAULT_NEXT_ROUND_DELAY = 2200
const DEFAULT_WRONG_FEEDBACK_DURATION = 900

export function normalizeGameAnswer(value = '') {
  return String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/\s+/g, '')
    .trim()
}

export function shuffleGameRounds(items = []) {
  const shuffled = [...items]

  for (let index = shuffled.length - 1; index > 0; index -= 1) {
    const swapIndex = Math.floor(Math.random() * (index + 1))
    const temp = shuffled[index]
    shuffled[index] = shuffled[swapIndex]
    shuffled[swapIndex] = temp
  }

  return shuffled
}

export function buildGameRounds(images = [], { filterMissingImage = false } = {}) {
  const rounds = images.map((image) => ({
    image: image?.urls?.preview || image?.urls?.original || image?.url || null,
    answer: image?.respuesta_correcta,
  }))

  return filterMissingImage ? rounds.filter((round) => round.image) : rounds
}

export function useGameSession({
  totalTime = DEFAULT_TOTAL_TIME,
  scorePerCorrect = DEFAULT_SCORE_PER_CORRECT,
  advanceOnWrongAnswer = false,
  shuffleOnStart = true,
  nextRoundDelay = DEFAULT_NEXT_ROUND_DELAY,
  wrongFeedbackDuration = DEFAULT_WRONG_FEEDBACK_DURATION,
  persistResult,
} = {}) {
  const rounds = ref([])
  const round = ref(1)
  const score = ref(0)
  const timeLeft = ref(resolveTotalTime(totalTime))
  const feedback = ref(null)
  const revealAnswer = ref(false)
  const answerDisabled = ref(false)
  const gameOver = ref(false)
  const answerInputRef = ref(null)

  const currentRound = computed(() => rounds.value[round.value - 1] ?? null)
  const totalRounds = computed(() => rounds.value.length)
  const progressPercent = computed(() => {
    if (!totalRounds.value) return 0

    const progress = ((round.value - 1) / totalRounds.value) * 100
    return Math.max(0, Math.min(100, progress))
  })

  const matchStartedAt = ref(null)
  const matchPersisted = ref(false)

  let timerInterval = null
  let wrongFeedbackTimeout = null
  let roundTransitionTimeout = null

  function resolveTotalTime(source) {
    const value = Number(unref(source) ?? DEFAULT_TOTAL_TIME)
    return Number.isFinite(value) && value > 0 ? value : DEFAULT_TOTAL_TIME
  }

  function stopTimer() {
    clearInterval(timerInterval)
    timerInterval = null
  }

  function clearWrongFeedbackTimeout() {
    clearTimeout(wrongFeedbackTimeout)
    wrongFeedbackTimeout = null
  }

  function clearRoundTransitionTimeout() {
    clearTimeout(roundTransitionTimeout)
    roundTransitionTimeout = null
  }

  function focusAnswerInput() {
    nextTick(() => answerInputRef.value?.focus())
  }

  function startTimer() {
    stopTimer()
    timeLeft.value = resolveTotalTime(totalTime)
    timerInterval = setInterval(() => {
      if (timeLeft.value <= 0) {
        stopTimer()
        onTimeout()
        return
      }

      timeLeft.value -= 1
    }, 1000)
  }

  function resetState() {
    clearWrongFeedbackTimeout()
    clearRoundTransitionTimeout()
    stopTimer()
    round.value = 1
    score.value = 0
    timeLeft.value = resolveTotalTime(totalTime)
    feedback.value = null
    revealAnswer.value = false
    answerDisabled.value = false
    gameOver.value = false
    matchStartedAt.value = null
    matchPersisted.value = false
  }

  async function startMatch(nextRounds = []) {
    resetState()
    const preparedRounds = Array.isArray(nextRounds) ? nextRounds : []
    rounds.value = shuffleOnStart ? shuffleGameRounds(preparedRounds) : preparedRounds

    if (rounds.value.length === 0) {
      return
    }

    matchStartedAt.value = new Date().toISOString()
    startTimer()
    focusAnswerInput()
  }

  function scheduleNextRound() {
    clearRoundTransitionTimeout()
    roundTransitionTimeout = setTimeout(async () => {
      if (round.value >= totalRounds.value) {
        await finishGame()
        return
      }

      round.value += 1
      feedback.value = null
      revealAnswer.value = false
      answerDisabled.value = false
      startTimer()
      focusAnswerInput()
    }, nextRoundDelay)
  }

  function onTimeout() {
    clearWrongFeedbackTimeout()
    answerDisabled.value = true
    revealAnswer.value = true
    feedback.value = 'timeout'
    scheduleNextRound()
  }

  function handleAnswer(value) {
    const receivedAnswer = normalizeGameAnswer(value)
    const expectedAnswer = normalizeGameAnswer(currentRound.value?.answer)

    if (receivedAnswer === expectedAnswer) {
      clearWrongFeedbackTimeout()
      stopTimer()
      answerDisabled.value = true
      revealAnswer.value = true
      score.value += scorePerCorrect
      feedback.value = 'correct'
      scheduleNextRound()
      return
    }

    if (advanceOnWrongAnswer) {
      clearWrongFeedbackTimeout()
      stopTimer()
      answerDisabled.value = true
      revealAnswer.value = true
      feedback.value = 'wrong'
      scheduleNextRound()
      return
    }

    feedback.value = 'wrong'
    revealAnswer.value = false
    answerDisabled.value = false
    clearWrongFeedbackTimeout()
    wrongFeedbackTimeout = setTimeout(() => {
      if (feedback.value === 'wrong') {
        feedback.value = null
      }
    }, wrongFeedbackDuration)
    focusAnswerInput()
  }

  async function finishGame() {
    stopTimer()
    clearWrongFeedbackTimeout()
    clearRoundTransitionTimeout()
    gameOver.value = true

    if (matchPersisted.value || typeof persistResult !== 'function') {
      return
    }

    matchPersisted.value = true

    try {
      await persistResult({
        score: score.value,
        startedAt: matchStartedAt.value ?? new Date().toISOString(),
        finishedAt: new Date().toISOString(),
      })
    } catch {
      matchPersisted.value = false
    }
  }

  onUnmounted(() => {
    stopTimer()
    clearWrongFeedbackTimeout()
    clearRoundTransitionTimeout()
  })

  return {
    rounds,
    round,
    totalRounds,
    progressPercent,
    score,
    timeLeft,
    feedback,
    revealAnswer,
    answerDisabled,
    gameOver,
    answerInputRef,
    currentRound,
    startMatch,
    handleAnswer,
    stopTimer,
  }
}

export default useGameSession