<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$counter = (string)($_GET['counter'] ?? '');
if (!in_array($counter, QMS_COUNTERS, true)) {
  $counter = QMS_COUNTERS[0] ?? 'Cashier';
}

$counterNum = array_search($counter, QMS_COUNTERS, true) + 1;
$accent = QMS_COUNTER_ACCENTS[$counter] ?? '#1c3268';

$pageTitle = QMS_ORG_NAME . ' • Counter • ' . $counter;
$bodyClass = 'wide counter-mode';
require __DIR__ . '/partials/layout_top.php';
?>

<div class="grid cols-2 counter-wrap" style="--accent: <?= htmlspecialchars($accent) ?>;" data-page="counter-client" data-counter="<?= htmlspecialchars($counter) ?>" data-counter-num="<?= htmlspecialchars($counterNum) ?>">
  <section class="card">
    <div class="card-h">
      <div>
        <div class="title">Counter: <?= htmlspecialchars($counter) ?></div>
        <div class="hint">Search for client or call next</div>
      </div>
      <div class="pill"><span class="dot"></span> Live</div>
    </div>
    <div class="card-b">
      <div class="grid cols-2">
        <div>
          <div class="label">NOW SERVING</div>
          <div class="big-number" id="cNow">—</div>
        </div>
        <div>
          <div class="label">WAITING COUNT</div>
          <div class="big-number" style="font-size:58px;" id="cWaitingCount">—</div>
          <div class="help">Clients waiting for this counter</div>
        </div>
      </div>

      <div class="row" style="margin-top:20px;">
        <button class="btn ok" id="btnCallNext" disabled>Call Next Client</button>
        <button class="btn danger" id="btnSkip" disabled>Skip Client</button>
      </div>

      <div class="card" style="margin-top:14px;">
        <div class="card-h">
          <div>
            <div class="title">Search Client</div>
            <div class="hint">Press Enter to search and assign</div>
          </div>
        </div>
        <div class="card-b">
          <div class="label">Client Surname (min 4 characters)</div>
          <input type="text" id="searchSurname" placeholder="Enter surname..." maxlength="255">
          <div id="searchError" class="help" style="color:red;display:none;"></div>
          <div class="row" style="margin-top:10px;">
            <button class="btn" id="btnSearch">Search</button>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px;">
        <div class="card-h">
          <div>
            <div class="title">Service Options</div>
            <div class="hint">For current client</div>
          </div>
        </div>
        <div class="card-b">
          <div class="row" style="margin-top:10px;">
            <button class="btn ok" id="btnFinish" disabled>Mark Complete</button>
            <button class="btn" id="btnRemove" disabled>Remove from Queue</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <aside class="card">
    <div class="card-h">
      <div>
        <div class="title">Waiting Queue</div>
        <div class="hint">Clients waiting (oldest first)</div>
      </div>
    </div>
    <div class="card-b">
      <div class="list" id="cWaiting"></div>
    </div>
  </aside>
</div>

<!-- Client Search Modal -->
<div id="searchModal" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Select Client</h3>
      <button class="close-btn" id="closeSearchModal">&times;</button>
    </div>
    <div class="modal-body">
      <div id="searchResults" class="list"></div>
      <button id="btnAddNew" class="btn" style="margin-top:10px;">Add New Client</button>
    </div>
  </div>
</div>

<!-- New Client Form Modal -->
<div id="newClientModal" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Register New Client</h3>
      <button class="close-btn" id="closeNewClientModal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="label">Surname (min 4 characters) *</div>
      <input type="text" id="newClientSurname" placeholder="Enter surname..." maxlength="255">
      
      <div class="label" style="margin-top:10px;">Full Name</div>
      <input type="text" id="newClientFullName" placeholder="Enter full name..." maxlength="500">
      
      <div class="label" style="margin-top:10px;">Phone</div>
      <input type="text" id="newClientPhone" placeholder="Enter phone..." maxlength="20">
      
      <div class="label" style="margin-top:10px;">Email</div>
      <input type="email" id="newClientEmail" placeholder="Enter email..." maxlength="255">
      
      <div id="newClientError" class="help" style="color:red;display:none;margin-top:10px;"></div>
      
      <div class="row" style="margin-top:15px;">
        <button class="btn ok" id="btnCreateAndAssign">Create & Assign to Queue</button>
        <button class="btn" id="btnCancelNewClient">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Purpose Selection Modal -->
<div id="purposeModal" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Service Purpose</h3>
      <button class="close-btn" id="closePurposeModal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="label">What is the purpose of this visit?</div>
      <input type="text" id="purposeInput" placeholder="Enter service purpose..." maxlength="500">
      
      <div class="row" style="margin-top:15px;">
        <button class="btn ok" id="btnAssignWithPurpose">Assign to Queue</button>
        <button class="btn" id="btnCancelPurpose">Cancel</button>
      </div>
    </div>
  </div>
</div>

<style>
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.list {
  max-height: 400px;
  overflow-y: auto;
}

.list-item {
  padding: 10px;
  margin-bottom: 8px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.list-item:hover {
  background-color: #f5f5f5;
}

.list-item.selected {
  background-color: var(--accent, #1c3268);
  color: white;
  border-color: var(--accent, #1c3268);
}
</style>

<script>
const counterNum = <?= json_encode((int)$counterNum) ?>;
const counter = <?= json_encode($counter) ?>;
let currentClient = null;
let currentQueueEntry = null;
let selectedClientForAssign = null;

document.getElementById('btnSearch').addEventListener('click', searchClients);
document.getElementById('searchSurname').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') searchClients();
});

document.getElementById('btnCallNext').addEventListener('click', callNextClient);
document.getElementById('btnFinish').addEventListener('click', finishService);
document.getElementById('btnSkip').addEventListener('click', skipClient);
document.getElementById('btnRemove').addEventListener('click', removeClient);
document.getElementById('closeSearchModal').addEventListener('click', () => closeModal('searchModal'));
document.getElementById('closeNewClientModal').addEventListener('click', () => closeModal('newClientModal'));
document.getElementById('closePurposeModal').addEventListener('click', () => closeModal('purposeModal'));
document.getElementById('btnAddNew').addEventListener('click', openNewClientForm);
document.getElementById('btnCreateAndAssign').addEventListener('click', createAndAssignClient);
document.getElementById('btnCancelNewClient').addEventListener('click', () => closeModal('newClientModal'));
document.getElementById('btnCancelPurpose').addEventListener('click', () => closeModal('purposeModal'));
document.getElementById('btnAssignWithPurpose').addEventListener('click', assignClientWithPurpose);

function searchClients() {
  const surname = document.getElementById('searchSurname').value.trim();
  const errorDiv = document.getElementById('searchError');
  
  if (surname.length < 4) {
    errorDiv.textContent = 'Surname must be at least 4 characters';
    errorDiv.style.display = 'block';
    return;
  }
  
  errorDiv.style.display = 'none';
  
  fetch('api.php?action=search_clients&surname=' + encodeURIComponent(surname))
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showSearchResults(data.clients);
      } else {
        errorDiv.textContent = data.error || 'Search failed';
        errorDiv.style.display = 'block';
      }
    });
}

function showSearchResults(clients) {
  const resultsDiv = document.getElementById('searchResults');
  
  if (clients.length === 0) {
    resultsDiv.innerHTML = '<p style="padding:10px;color:#666;">No clients found</p>';
  } else {
    resultsDiv.innerHTML = clients.map(client => 
      `<div class="list-item" onclick="selectClientForAssign(${client.id}, '${client.surname}')">
        <strong>${client.surname}</strong> <br>
        <small>${client.full_name || ''} | ${client.phone || 'N/A'}</small>
      </div>`
    ).join('');
  }
  
  openModal('searchModal');
}

function selectClientForAssign(clientId, surname) {
  selectedClientForAssign = { id: clientId, surname };
  closeModal('searchModal');
  openModal('purposeModal');
}

function openNewClientForm() {
  closeModal('searchModal');
  openModal('newClientModal');
  document.getElementById('newClientSurname').focus();
}

function createAndAssignClient() {
  const surname = document.getElementById('newClientSurname').value.trim();
  const fullName = document.getElementById('newClientFullName').value.trim() || surname;
  const phone = document.getElementById('newClientPhone').value.trim();
  const email = document.getElementById('newClientEmail').value.trim();
  const errorDiv = document.getElementById('newClientError');
  
  if (surname.length < 4) {
    errorDiv.textContent = 'Surname must be at least 4 characters';
    errorDiv.style.display = 'block';
    return;
  }
  
  fetch('api.php?action=create_client', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ surname, full_name: fullName, phone, email })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      selectedClientForAssign = { id: data.id, surname: data.surname };
      document.getElementById('newClientSurname').value = '';
      document.getElementById('newClientFullName').value = '';
      document.getElementById('newClientPhone').value = '';
      document.getElementById('newClientEmail').value = '';
      errorDiv.style.display = 'none';
      closeModal('newClientModal');
      openModal('purposeModal');
    } else {
      errorDiv.textContent = data.error || 'Failed to create client';
      errorDiv.style.display = 'block';
    }
  });
}

function assignClientWithPurpose() {
  if (!selectedClientForAssign) return;
  
  const purpose = document.getElementById('purposeInput').value.trim();
  
  fetch('api.php?action=assign_client', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      client_id: selectedClientForAssign.id,
      counter: counterNum,
      purpose: purpose
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('purposeInput').value = '';
      closeModal('purposeModal');
      document.getElementById('searchSurname').value = '';
      updateCounterState();
    } else {
      alert('Error: ' + (data.error || 'Failed to assign client'));
    }
  });
}

function callNextClient() {
  fetch('api.php?action=call_next_client', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ counter: counterNum })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      updateCounterState();
    } else {
      alert(data.message || 'No clients in queue');
    }
  });
}

function finishService() {
  if (!currentQueueEntry) return;
  
  fetch('api.php?action=complete_service', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ entry_id: currentQueueEntry })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      updateCounterState();
    } else {
      alert('Error: ' + (data.error || 'Failed to complete service'));
    }
  });
}

function skipClient() {
  if (!currentQueueEntry) return;
  
  if (confirm('Skip this client and keep them in queue?')) {
    fetch('api.php?action=call_next_client', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ counter: counterNum })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        updateCounterState();
      } else {
        alert('Error: ' + (data.error || 'Failed to skip'));
      }
    });
  }
}

function removeClient() {
  if (!currentQueueEntry) return;
  
  if (confirm('Remove this client from queue?')) {
    // Implementation for removing client
    updateCounterState();
  }
}

function updateCounterState() {
  fetch('api.php?action=counter_queue&counter=' + counterNum)
    .then(r => r.json())
    .then(data => {
      if (data.ok && data.queue) {
        const queue = data.queue;
        
        if (queue.length > 0) {
          const first = queue[0];
          document.getElementById('cNow').textContent = first.surname;
          currentClient = first;
          currentQueueEntry = first.id;
          document.getElementById('btnFinish').disabled = false;
          document.getElementById('btnSkip').disabled = false;
          document.getElementById('btnRemove').disabled = false;
          document.getElementById('btnCallNext').disabled = true;
        } else {
          document.getElementById('cNow').textContent = '—';
          document.getElementById('btnFinish').disabled = true;
          document.getElementById('btnSkip').disabled = true;
          document.getElementById('btnRemove').disabled = true;
          document.getElementById('btnCallNext').disabled = false;
          currentClient = null;
          currentQueueEntry = null;
        }
        
        document.getElementById('cWaitingCount').textContent = queue.length;
        
        // Update waiting list
        const waitingList = document.getElementById('cWaiting');
        if (queue.length <= 1) {
          waitingList.innerHTML = '<p style="color:#999;">No one waiting</p>';
        } else {
          waitingList.innerHTML = queue.slice(1).map((client, idx) =>
            `<div class="list-item" style="padding:8px;">
              <strong>${idx + 2}. ${client.surname}</strong>
              <div style="font-size:0.9em;color:#666;">${client.full_name || ''}</div>
            </div>`
          ).join('');
        }
      }
    });
}

function openModal(modalId) {
  document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Update every 2 seconds
setInterval(updateCounterState, 2000);
updateCounterState();
</script>

<?php require __DIR__ . '/partials/layout_bottom.php'; ?>
