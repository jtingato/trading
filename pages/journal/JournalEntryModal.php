<div id="addEntryModal" class="modal-overlay">
  <div class="modal">
    <h3>Add New Journal Entry</h3>

    <div class="form-grid">
      <input id="new-symbol" placeholder="Symbol" />
      <input id="new-quantity" type="number" placeholder="Quantity" />

      <select id="new-asset-type"></select>
      <select id="new-side"></select>

      <input id="new-price" type="number" step="0.01" placeholder="Price Per Unit" />
      <input id="new-datetime" type="datetime-local" />

      <select id="new-broker"></select>
      <input id="new-strategy" placeholder="Strategy" />

      <textarea id="new-notes" placeholder="Notes"></textarea>
    </div>

    <div class="modal-actions">
      <button id="cancelAddEntry">Cancel</button>
      <button id="saveAddEntry">Save Entry</button>
    </div>
  </div>
</div>