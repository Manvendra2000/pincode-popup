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
      <span class="location-icon"> <img src="https://www.jiomart.com/assets/ds2web/jds-icons/location-gray-icon.svg" alt=""> </span>
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
      if (data && data.success && data.data && Array.isArray(data.data.data) && data.data.data.length) {
        data.data.data.slice(0, 5).forEach((item) => {
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
      width: 500px;
      /* text-align: center; */
      box-shadow: 0 4px 14px rgb(0 0 0 / 0.5);
      margin: 130px 0 0 50px;
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
    .location-icon {
      position: absolute;
      left: 10px; 
      top: 21px; 
      transform: translateY(-50%); 
      font-size: 18px;
    }
  </style>
  <?php
  return ob_get_clean();
});


add_action('pre_get_posts', 'filter_products_by_user_state_from_cookie');
function filter_products_by_user_state_from_cookie($query) {
    if (is_admin() || !$query->is_main_query()) return;

    if (!is_post_type_archive('product') && !is_tax('product_cat')) return;

    if (!isset($_COOKIE['address'])) return;

    // Get current queried category
    $queried_object = get_queried_object();
    $current_category = isset($queried_object->name) ? strtolower($queried_object->name) : '';

    // Only filter if main category is Milk or Curd and Yogurt
    if (!in_array($current_category, ['milk', 'curd and yogurt'])) return;

    $address = sanitize_text_field(urldecode($_COOKIE['address']));
    $parts = explode(',', $address);
    $state = trim($parts[count($parts) - 2] ?? '');

    if (empty($state)) return;

    $tax_query = $query->get('tax_query') ?: [];

    $tax_query[] = array(
        'taxonomy' => 'product_cat',
        'field'    => 'name',
        'terms'    => $state,
        'operator' => 'IN',
    );

    $query->set('tax_query', $tax_query);
}




add_shortcode('delivery_eta_box', function() {
  if (!isset($_COOKIE['address'])) return '';

  $address = sanitize_text_field(urldecode($_COOKIE['address']));
  $parts = explode(',', $address);

  $city = trim($parts[0] ?? '');
  $state = trim($parts[1] ?? '');

  $pin = '';
  if ($city) {
      $response = wp_remote_get("http://www.postalpincode.in/api/postoffice/" . urlencode($city));
      if (!is_wp_error($response)) {
          $data = json_decode(wp_remote_retrieve_body($response), true);
          if ($data['Status'] === 'Success' && !empty($data['PostOffice'])) {
              foreach ($data['PostOffice'] as $office) {
                  if (isset($office['State']) && strtolower($office['State']) === strtolower($state)) {
                      $pin = $office['PINCode'];
                      break;
                  }
              }
          }
      }
  }

  $eta = rand(15, 30);
  $location = $pin ? "$pin, $city" : $city;

return '<div style="
  align-items: center;
  background: #f2f2f2;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 13px;
  color: #000;
  width: fit-content;
  font-weight: 500;
  line-height: 20px;
">
  🚚 Get it in ' . $eta . ' mins<br>
  <strong>' . esc_html($location) . '</strong>
</div>';
});