<script setup>
import { onMounted, ref } from "vue";
import { RouterView, useRouter } from "vue-router";
import { useStore } from "./store";
import ClickSpark from "./pages/components/ClickSpark.vue";
onMounted(() => {
  useStore().startTokenExpirationMonitor();
  // if (useStore().isLogout) {
  //   useRouter().push({ name: "Login" });
  // }
});
</script>
<template>
  <div>
    <RouterView />
    <ClickSpark
      :sparkColor="'#ff4032'"
      :sparkSize="2"
      :sparkRadius="5"
      :sparkCount="10"
      :duration="200"
    />
  </div>
  <!-- <ClickSpark
    :sparkColor="'#00ff0f'"
    :sparkSize="10"
    :sparkRadius="15"
    :sparkCount="8"
    :duration="400"
  >
    <RouterView />
  </ClickSpark> -->
</template>
