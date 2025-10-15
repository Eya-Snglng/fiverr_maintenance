const ITEMS = {
  'peewee-sizzling-bbq': { name: 'PeeWee Sizzling BBQ', price: 1.50 },
  'ri-chee-crunchy-snack': { name: 'Ri-Chee Crunchy Snack', price: 0.95 },
  'viva-caramel-candy': { name: 'Viva Caramel Candy', price: 0.60 },
};

function formatMoney(value) { return `$${value.toFixed(2)}`; }

async function submitOrder(e) {
  e.preventDefault();
  const form = e.currentTarget;
  const itemKey = form.dataset.item;
  const cashInput = form.querySelector('input[name="cash"]');
  const qtyInput = form.querySelector('input[name="quantity"]');
  const output = form.querySelector('.output');

  const cash = parseFloat(cashInput.value);
  const quantity = parseInt(qtyInput.value, 10);

  output.textContent = 'Processing...';
  output.className = 'output';

  try {
    const res = await fetch('/api/purchase.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item: itemKey, cash, quantity })
    });
    const data = await res.json();

    if (data.success) {
      output.innerHTML = `✅ Success! Total: ${formatMoney(data.total)} | Change: ${formatMoney(data.change)}`;
      output.classList.add('success');
    } else {
      output.textContent = `❌ ${data.error || 'Transaction failed.'}`;
      output.classList.add('error');
    }
  } catch (err) {
    output.textContent = '❌ Network or server error.';
    output.classList.add('error');
  }
}

function init() {
  document.querySelectorAll('form.order-form').forEach(form => {
    form.addEventListener('submit', submitOrder);
  });
}

document.addEventListener('DOMContentLoaded', init);
