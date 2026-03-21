document.addEventListener('DOMContentLoaded', function () {
  setupPasswordToggle();
  setupHomeActions();
  setupDashboardTabs();
  setupChecklistProgress();
  setupMedicationActions();
  setupCommonButtons();
});

function setupPasswordToggle() {
  const toggleBtn = document.querySelector('[data-toggle-password]');
  const passwordInput = document.querySelector('#password');

  if (!toggleBtn || !passwordInput) return;

  toggleBtn.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    toggleBtn.textContent = isPassword ? '🙈' : '👁';
  });
}

function setupHomeActions() {
  const searchBtn = document.querySelector('.topbar-right .ghost-btn.small');
  if (searchBtn && searchBtn.textContent.includes('Search contacts')) {
    searchBtn.addEventListener('click', function () {
      window.location.href = 'contacts.html';
    });
  }

  document.querySelectorAll('button').forEach(function (btn) {
    const label = btn.textContent.trim();

    if (label === 'Read More') {
      btn.addEventListener('click', function () {
        const card = btn.closest('.card');
        const title = card?.querySelector('h3')?.textContent?.trim() || 'this tip';
        const description = card?.querySelector('p')?.textContent?.trim() || '';
        alert(`More about "${title}"\n\n${description}`);
      });
    }

    if (label === 'Manage Meds') {
      btn.addEventListener('click', function () {
        window.location.href = 'dashboard.html#medication';
      });
    }

    if (label === 'View Tasks') {
      btn.addEventListener('click', function () {
        window.location.href = 'dashboard.html#checklist';
      });
    }

    if (label === 'View Calendar') {
      btn.addEventListener('click', function () {
        window.location.href = 'dashboard.html#appointments';
      });
    }
  });
}

function setupDashboardTabs() {
  const tabButtons = document.querySelectorAll('.activity-tab');
  if (!tabButtons.length) return;

  const badge = document.getElementById('currentActivityBadge');

  function activateTab(target) {
    document.querySelectorAll('.activity-panel').forEach(function (panel) {
      panel.classList.toggle('activity-panel--active', panel.id === `panel-${target}`);
    });

    document.querySelectorAll('.side-panel').forEach(function (panel) {
      panel.classList.toggle('side-panel--active', panel.id === `side-${target}`);
    });

    tabButtons.forEach(function (btn) {
      btn.classList.toggle('activity-tab--active', btn.dataset.target === target);
    });

    if (badge) {
      const activeBtn = Array.from(tabButtons).find(function (btn) {
        return btn.dataset.target === target;
      });
      badge.textContent = activeBtn ? activeBtn.textContent.trim() : 'Activity';
    }

    window.location.hash = target;
  }

  tabButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      activateTab(btn.dataset.target);
    });
  });

  const hashTarget = window.location.hash.replace('#', '');
  const validTargets = ['checklist', 'medication', 'appointments', 'contacts'];
  activateTab(validTargets.includes(hashTarget) ? hashTarget : 'checklist');
}

function setupChecklistProgress() {
  const checklistItems = document.querySelectorAll('.checklist-item');
  const progressFill = document.querySelector('.checklist-progress');
  const badge = document.querySelector('.checklist-badge');

  if (!checklistItems.length || !progressFill || !badge) return;

  function updateProgress() {
    const total = checklistItems.length;
    const completed = Array.from(checklistItems).filter(function (item) {
      return item.checked;
    }).length;

    const percent = Math.round((completed / total) * 100);
    progressFill.style.width = `${percent}%`;
    badge.textContent = `${percent}% Completed`;
  }

  checklistItems.forEach(function (item) {
    item.addEventListener('change', updateProgress);
  });

  updateProgress();
}

function setupMedicationActions() {
  const addBtn = document.getElementById('addMedicationBtn');
  const medList = document.getElementById('medList');
  const nameInput = document.getElementById('medName');
  const doseInput = document.getElementById('medDose');

  if (addBtn && medList && nameInput && doseInput) {
    addBtn.addEventListener('click', function () {
      const name = nameInput.value.trim();
      const dose = doseInput.value.trim();

      if (!name || !dose) {
        alert('Please enter both medication name and dosage.');
        return;
      }

      const item = document.createElement('li');
      item.className = 'med-item';
      item.innerHTML = `
        <div>
          <strong>${escapeHtml(name)}</strong>
          <p class="muted tiny">${escapeHtml(dose)} · Custom schedule</p>
        </div>
        <button class="ghost-btn tiny mark-taken-btn">Mark Taken</button>
      `;

      medList.appendChild(item);
      attachMarkTaken(item.querySelector('.mark-taken-btn'));

      nameInput.value = '';
      doseInput.value = '';
    });
  }

  document.querySelectorAll('.mark-taken-btn').forEach(function (btn) {
    attachMarkTaken(btn);
  });
}

function attachMarkTaken(button) {
  if (!button) return;

  button.addEventListener('click', function () {
    const item = button.closest('.med-item');
    if (!item || item.classList.contains('med-item--success')) return;

    item.classList.add('med-item--success');
    button.remove();

    const status = document.createElement('span');
    status.className = 'status-pill success';
    status.textContent = 'Completed';
    item.appendChild(status);
  }, { once: true });
}

function setupCommonButtons() {
  document.querySelectorAll('[data-action="history"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      alert('Showing history is not implemented yet.');
    });
  });

  const reportBtn = document.querySelector('[data-action="report"]');
  if (reportBtn) {
    reportBtn.addEventListener('click', function () {
      alert('Generating daily care report...');
    });
  }

  const scheduleNewBtn = document.getElementById('scheduleNewBtn');
  if (scheduleNewBtn) {
    scheduleNewBtn.addEventListener('click', function () {
      alert('Appointment scheduling is a demo action for now.');
    });
  }

  document.querySelectorAll('.contact-call-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const card = btn.closest('.emergency-contact');
      const name = card?.querySelector('.contact-name')?.textContent?.trim() || 'Contact';
      const phoneNodes = card ? card.querySelectorAll('.tiny.muted, .tiny.muted-light') : [];
      const phone = phoneNodes.length ? phoneNodes[phoneNodes.length - 1].textContent.trim() : 'Unknown number';
      alert(`Calling ${name} at ${phone}`);
    });
  });
}

function escapeHtml(value) {
  const div = document.createElement('div');
  div.textContent = value;
  return div.innerHTML;
}


window.showStretch = function () {
  alert('Morning Stretch\n\nSpend 5 minutes on light movement to reduce stiffness and improve circulation.');
};
