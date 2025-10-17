<script setup>
import { onMounted, ref } from "vue";
import { api } from "@/utils/axios";

const props = defineProps({
  newStudentName: {
    type: String,
  },
  newStudentId: {
    type: String,
  },
});

console.log(props);

const months = ref([]);

const getMonth = async () => {
  try {
    await api.post("get_all_month").then((res) => {
      months.value = res.data;
    });
  } catch (error) {
    console.log(error);
  }
};

const form = ref({
  student_id: props.newStudentId,
  month_id: null,
  class_id: null,
  avg_m: null,
  listent: null,
  speaking: null,
  writing: null,
  reading: null,
  essay: null,
  grammar: null,
  math: null,
  chemistry: null,
  physical: null,
  history: null,
  morality: null,
  art: null,
  word: null,
  pe: null,
  homework: null,
  healthy: null,
  steam: null,
});

const addNewstudentScore = () => {
  console.log("score", form.value);
};

onMounted(() => {
  getMonth();
});
</script>
<template>
  <div>
    <VCard>
      <VCardTitle class="customKhmerMoul text-white bg-green-darken-4 py-3"
        >បញ្ចូលពិន្ទុសិស្សថ្មី</VCardTitle
      >
      <VCardText class="mt-4">
        <VRow>
          <VCol cols="12" md="12">
            <h2 class="text-center customFont font-weight-bold">
              {{ props.newStudentName }}
            </h2>
          </VCol>
          <VCol cols="12" sm="6" md="3">
            <VSelect
              :items="months"
              item-value="id"
              item-title="name_kh"
              class="customFont"
              density="compact"
              placeholder="៦"
              label="ប្រចាំខែ"
              variant="outlined"
              v-model="form.month_id"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardText style="margin-top: -20px; margin-bottom: 10px">
        <VTable fixed-header class="border customFont">
          <thead class="my-custom-table">
            <tr>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                ស្ដាប់
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                និយាយ
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                អំណាន
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                សរសេរ
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                តែងសេចក្ដី
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                វេយ្យាករណ៍
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                គណិតវិទ្យា
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                វិទ្យាសាស្រ្ត
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                ភូមិវិទ្យា
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                ប្រវិត្តិវិទ្យា
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                សីលធម៍
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                គំនូរ
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                អក្សរផ្ចង់
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                អប់រំកាយ
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                កិច្ចការផ្ទះ
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                អនាម័យ
              </th>
              <th style="height: 0px" class="text-left py-2 px-8 border">
                STEAM
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border">
                <VTextField
                  v-model="form.listent"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.speaking"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.reading"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.writing"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.essay"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.grammar"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.math"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.chemistry"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.physical"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.history"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.morality"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.art"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.word"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.pe"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.homework"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.healthy"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
              <td class="border">
                <VTextField
                  v-model="form.steam"
                  hide-details
                  density="compact"
                  variant="outlined"
                />
              </td>
            </tr>
          </tbody>
        </VTable>
        <div class="d-flex ga-3 justify-end mt-4">
          <!-- v-if="isSecondary || isHighschool" -->
          <div style="width: 200px">
            <VTextField
              class="customFont"
              density="compact"
              label="បញ្ចូលតួចែក"
              variant="outlined"
              v-model="form.avg_m"
            />
          </div>
          <v-btn
            variant="tonal"
            class="customFont"
            color="green"
            @click="addNewstudentScore"
            >បញ្ចូលពិន្ទុ</v-btn
          >
        </div>
      </VCardText>
    </VCard>
  </div>
</template>
