<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = defineProps({
  text: {
    type: String,
    required: true,
  },
  speed: {
    type: Number,
    default: 50,
  },
});

const displayedText = ref('');
let i = 0;

const typeWriter = () => {
  if (i < props.text.length) {
    displayedText.value += props.text.charAt(i);
    i++;
    setTimeout(typeWriter, props.speed);
  }
};

onMounted(() => {
  typeWriter();
});
</script>
