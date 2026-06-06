import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// DO NOT call Alpine.start() here — Livewire 4 starts Alpine automatically.
// Calling it twice causes "multiple instances" warning and erratic behavior.
