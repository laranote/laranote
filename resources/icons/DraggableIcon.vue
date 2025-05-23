<script setup>
defineProps({
    width: { type: Number, default: 15 },
    height: { type: Number, default: 15 },
})
</script>

<template>
    <svg viewBox="0 0 512 512" :width="width" :height="height" version="1.1" xmlns="http://www.w3.org/2000/svg">
        <path fill="currentColor" stroke="currentColor" stroke-width="1"
              d="M 72.5 128 L 439.5 128 Q 450.4 130.6 456 138.5 Q 461.3 144.7 461 156.5 Q 459.1 166.2 454 172 L 440.5 179 L 71.5 179 L 58 172 L 53 163.5 L 51 156.5 L 51 150.5 Q 53.2 138.7 61.5 133 L 72.5 128 Z "/>
        <path fill="currentColor" stroke="currentColor" stroke-width="1"
              d="M 77.5 230 L 434.5 230 L 444.5 232 Q 451.3 234.3 455 239.5 Q 461.3 245.7 461 258.5 Q 458.8 269.8 451.5 276 L 442.5 281 Q 436.3 279.3 434.5 282 L 77.5 282 Q 75.8 279.3 69.5 281 L 57 272.5 Q 50.7 266.3 51 253.5 Q 53.4 241.4 61.5 235 L 66.5 232 L 77.5 230 Z "/>
        <path fill="currentColor" stroke="currentColor" stroke-width="1"
              d="M 71.5 333 L 440.5 333 L 454 340 L 459 348.5 L 461 355.5 L 461 361.5 Q 458.8 374.3 449.5 380 L 439.5 384 L 72.5 384 Q 60.6 381.4 55 372.5 L 51 361.5 L 51 355.5 Q 52.9 345.8 58 340 L 71.5 333 Z "/>
        <path fill="none" stroke="none"
              d="M 0 0 L 512 0 L 512 512 L 0 512 L 0 0 Z M 73 128 L 62 133 Q 53 139 51 151 L 51 157 L 53 164 L 58 172 L 72 179 L 441 179 L 454 172 Q 459 166 461 157 Q 461 145 456 139 Q 450 131 440 128 L 73 128 Z M 78 230 L 67 232 L 62 235 Q 53 241 51 254 Q 51 266 57 273 L 70 281 Q 76 279 78 282 L 435 282 Q 436 279 443 281 L 452 276 Q 459 270 461 259 Q 461 246 455 240 Q 451 234 445 232 L 435 230 L 78 230 Z M 72 333 L 58 340 Q 53 346 51 356 L 51 362 L 55 373 Q 61 381 73 384 L 440 384 L 450 380 Q 459 374 461 362 L 461 356 L 459 349 L 454 340 L 441 333 L 72 333 Z "/>
    </svg>
</template>

<style scoped>

</style>
