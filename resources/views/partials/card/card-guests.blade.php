<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6" >
  <!-- Total Guests -->
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
      Total Guests
    </p>

    <div class="mt-3 flex items-end justify-between">
      <div>
        <h4 id="totalGuests" class="text-2xl font-bold text-gray-800 dark:text-white/90">
          {{ $totalGuests ?? 0 }}
        </h4>
      </div>

      <div class="flex items-center gap-1">
        <span class="flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-theme-xs font-medium text-primary dark:bg-primary/15 dark:text-primary">
          <i class="fas fa-users text-xs"></i>
        </span>
        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
          All Time
        </span>
      </div>
    </div>
  </div>

  <!-- Active Guests -->
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
      Active Guests
    </p>

    <div class="mt-3 flex items-end justify-between">
      <div>
        <h4 id="activeGuests" class="text-2xl font-bold text-gray-800 dark:text-white/90">
          {{ $activeGuests ?? 0 }}
        </h4>
      </div>

      <div class="flex items-center gap-1">
        <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
          <i class="fas fa-bed text-xs"></i>
        </span>
        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
          Currently Staying
        </span>
      </div>
    </div>
  </div>

  <!-- Check-ins Today -->
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
      Check-ins Today
    </p>

    <div class="mt-3 flex items-end justify-between">
      <div>
        <h4 id="checkinsToday" class="text-2xl font-bold text-gray-800 dark:text-white/90">
          {{ $checkinsToday ?? 0 }}
        </h4>
      </div>

      <div class="flex items-center gap-1">
        <span class="flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
          <i class="fas fa-calendar-check text-xs"></i>
        </span>
        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
          Daily
        </span>
      </div>
    </div>
  </div>

  <!-- Check-outs Today -->
  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <p class="text-theme-sm text-gray-500 dark:text-gray-400">
      Check-outs Today
    </p>

    <div class="mt-3 flex items-end justify-between">
      <div>
        <h4 id="checkoutsToday" class="text-2xl font-bold text-gray-800 dark:text-white/90">
          {{ $checkoutsToday ?? 0 }}
        </h4>
      </div>

      <div class="flex items-center gap-1">
        <span class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
          <i class="fas fa-calendar-times text-xs"></i>
        </span>
        <span class="text-theme-xs text-gray-500 dark:text-gray-400">
          Daily
        </span>
      </div>
    </div>
  </div>
</div>

<script>
// Guest Stats Update Functions
function updateGuestStats(stats) {
    // Update card elements directly
    const totalGuestsEl = document.getElementById('totalGuests');
    const activeGuestsEl = document.getElementById('activeGuests');
    const checkinsTodayEl = document.getElementById('checkinsToday');
    const checkoutsTodayEl = document.getElementById('checkoutsToday');
    
    if (totalGuestsEl) totalGuestsEl.textContent = stats.totalGuests || 0;
    if (activeGuestsEl) activeGuestsEl.textContent = stats.activeGuests || 0;
    if (checkinsTodayEl) checkinsTodayEl.textContent = stats.checkinsToday || 0;
    if (checkoutsTodayEl) checkoutsTodayEl.textContent = stats.checkoutsToday || 0;
}

// Function to update stats based on filter changes
async function fetchGuestStats(filters = {}) {
    try {
        const params = new URLSearchParams(filters);
        const response = await fetch(`{{ route('guests.index') }}?${params}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.stats) {
                updateGuestStats(data.stats);
                return data.stats;
            }
        }
    } catch (error) {
        console.error('Error fetching guest stats:', error);
    }
    return null;
}

// Initialize stats when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // If there's a global guestTable component, listen for its updates
    if (window.guestTable) {
        // Override guestTable's updateStatsCards to use our function
        const originalUpdateStats = window.guestTable.updateStatsCards;
        window.guestTable.updateStatsCards = function(stats) {
            updateGuestStats(stats);
            if (originalUpdateStats) {
                originalUpdateStats.call(this, stats);
            }
        };
    }
});
</script>