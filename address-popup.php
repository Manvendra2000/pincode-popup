<?php
/**
 * Plugin Name: Address autocomplete Popup
 * Description: Shows a address autocomplete popup until address is set in cookies.
 * Version: 1.0
 * Text Domain: address-popup
 */

// Shortcode [address-popup]
add_shortcode('address-popup', function(){
  if (isset($_COOKIE['address'])) {
      // Do not show if already set
      return '';
  }

  ob_start();
  ?>
  <div id="address-popup">
    <div class="address-box">
      <h2>Enter Delivery Address</h2>
      <p>Start typing your address to see delivery options.</p>

      <div class="input-wrapper">
        <input id="address_input" type="text" placeholder="Enter your address">
        <ul id="address_suggestions" class="suggestion-list" style="list-style: none; padding: 0;margin-bottom:15px;border:1px solid #ccc;background:#fff;color:#000;"></ul>
        <button id="address_submit">Save</button>
      </div>
    </div>
  </div>

  <script>
 document.addEventListener('DOMContentLoaded',(function(){
  if (document.cookie.indexOf('address')===-1){
    document.getElementById('address-popup').style.display='flex';
  } else {
    document.getElementById('address-popup').style.display='none';
  }

  const input = document.getElementById('address_input');
  const suggestions = document.getElementById('address_suggestions');
  let selected = '';
  
  input.addEventListener('input',(e)=>{
    const query = e.target.value.trim();
    if (query.length < 3) return;

    fetch('https://ajdui8.in/demo2/wp-content/plugins/address-popup/fetch-places.php?input=' + encodeURIComponent(query) + '&lat=12.9716&lng=77.5946')
  .then(r => r.json()) 
  .then(data => {
    console.log(data);
    suggestions.innerHTML = '';
    if (data && data.data && data.data.length) {
      data.data.slice(0, 5).forEach((item) => {
        const li = document.createElement('li');
        li.textContent = item.description;
        li.dataset.placeId = item.place_id;
        li.onclick = () => {
          selected = item.description;
          input.value = selected;
          suggestions.innerHTML = '';
        };
        suggestions.appendChild(li);
      });
    }
  })
  .catch(error => console.error(error));

  });

  document.getElementById('address_submit').addEventListener('click',(e)=>{
    e.preventDefault();

    if (selected.length > 0) {
      document.cookie = "address=" + encodeURIComponent(selected) + ";path=/;max-age=2592000";
      document.getElementById('address-popup').style.display='none';
      location.reload();
    } else {
      alert('Please select an address.');
    }
  });
}))
  </script>

  <style>
    #address-popup {
      position:fixed;
      top:0; left:0; bottom:0; right:0;
      background:rgba(0,0,0,0.7);
      z-index:9999;
      color:#fff;
      display:flex;
      align-items:flex-start;
      justify-content:flex-start;
    }
    .address-box {
      position: relative;
      background: #fff;
      color: #000;
      padding: 30px 20px;
      border-radius: 20px;
      width: 300px;
      text-align: center;
      box-shadow: 0 4px 14px rgb(0 0 0 / 0.5);
      margin: 50px 0 0 50px;
    }
    .address-box::after {
      content:'';
      position: absolute;
      bottom: 100%;
      left: 20px;
      border-bottom: 20px solid #fff;
      border-right: 20px solid transparent;
      border-left: 20px solid transparent;
    }
    .input-wrapper {
      position: relative;
      margin-bottom: 20px;
    }
    .input-wrapper input {
      width: 100%;
      padding: 10px 10px 10px 40px;
      border-radius: 12px;
      border: 1px solid #ccc;
      outline: none;
      font-size: 16px;
    }
    .input-wrapper button {
      margin-top: 15px;
      padding: 10px 20px;
      background: #007bff;
      color: #fff;
      font-size: 16px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .input-wrapper button:hover {
      background: #0056b3;
    }
    .suggestion-list li {
      padding: 10px;
      border-bottom: 1px solid #ccc;
      cursor: pointer;
    }
    .suggestion-list li:hover {
      background: #f5f5f5;
    }
  </style>
  <?php
  return ob_get_clean();
});