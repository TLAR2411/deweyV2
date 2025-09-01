<script setup>
import { ref } from "vue";
const props = defineProps({
  students_scores: {
    type: Array,
    required: true,
  },
  level: {
    type: Number,
  },
});
const subject = ref([
  { name: "ឈ្មោះសិស្ស" },
  { name: "តែងសេចក្ដី" },
  { name: "ស.ស.អាន" },
  { name: "ភាសាខ្មែរ" },
  { name: "សីលធម៌" },
  { name: "ប្រវត្តិវិទ្យា" },
  { name: "ភូមិវិទ្យា" },
  { name: "គណិតវិទ្យា" },
  { name: "រូបវិទ្យា" },
  { name: "គីមីវិទ្យា" },
  { name: "ជីវវិទ្យា" },
  { name: "ផែនដីវិទ្យា" },
  { name: "គេហវិទ្យា" },
  { name: "កីឡា" },
  { name: "អង់គ្លេស" },
  { name: "កុំព្យូទ័រ" },
  { name: "សកម្មភាព" },
]);

const scoreFields = [
  "writing",
  "essay",
  "khmer",
  "morality",
  "history",
  "geography",
  "math",
  "physical",
  "chemistry",
  "biology",
  "geology",
  "house_education",
  "pe",
  "english",
  "computer",
];

// function for copy and paste data to table field
const handlePaste = (event, rowIndex, startField) => {
  event.preventDefault();

  const clipboardData = event.clipboardData || window.clipboardData;
  const pasteValue = clipboardData.getData("text");
  const values = pasteValue.trim().split(/\t|\n/);

  const student = props.students_scores[rowIndex];
  const level = Number(props.level);

  console.log("Pasted values:", values);
  console.log("Target student:", student);

  // Find the index of the field where paste started
  let startIndex = scoreFields.indexOf(startField);

  if (startIndex === -1) {
    console.warn(`Invalid start field: ${startField}`);
    return;
  }

  // Begin pasting from the correct field
  for (let i = startIndex; i < scoreFields.length; i++) {
    const field = scoreFields[i];

    // Skip fields based on level conditions
    if (field === "pe" && level === 9) {
      continue;
    }

    if (values.length > 0) {
      const value = values.shift();
      student[field] = value.trim();
      console.log(`Assigned ${value} to ${field}`);
    } else {
      break;
    }
  }
};

console.log("propLevel", typeof props.level);
</script>

<template>
  <v-table fixed-header height="460" class="border customFont">
    <thead class="my-custom-table">
      <tr>
        <!-- <th class="text-left py-2" style="height: 0px">ឈ្មោះសិស្ស</th> -->
        <th
          style="height: 0px"
          v-for="s in subject"
          :key="s"
          class="text-left py-2 px-8 border"
        >
          {{ s.name }}
        </th>
      </tr>
    </thead>

    <tbody class="my-custom-table">
      <tr v-for="(item, rowIndex) in students_scores" :key="item.id">
        <td>
          {{ item.kh_name }}
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'writing')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.writing"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'essay')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.essay"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'khmer')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.khmer"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'morality')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.morality"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'history')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.history"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'geography')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.geography"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'math')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.math"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'physical')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.physical"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'chemistry')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.chemistry"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'biology')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.biology"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'geology')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.geology"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'house_education')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.house_education"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'pe')"
            :readonly="props.level == 9"
            :class="props.level == 9 ? 'bg-grey-lighten-3' : ''"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.pe"
          />
        </td>
        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'english')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.english"
          />
        </td>

        <td class="border">
          <VTextField
            @paste.native="(e) => handlePaste(e, rowIndex, 'computer')"
            hide-details
            density="compact"
            variant="outlined"
            v-model="item.computer"
          />
        </td>
        <td class="border text-center">
          <VBtn
            variant="outlined"
            color="red"
            @click="$emit('deleteRecord', item.id)"
            >លុប</VBtn
          >
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
