// English / Hindi switch. Translates visible text, placeholders and button values by exact match.
// Add a phrase: put "English text": "हिंदी" in HI. Trailing ":" and "*" are kept automatically.
(function () {
  const HI = {
    "Dashboard": "डैशबोर्ड", "Home": "होम", "Log out": "लॉग आउट", "Logout": "लॉग आउट", "Log Out": "लॉग आउट",
    "Customers": "ग्राहक", "Customer": "ग्राहक", "All Customers": "सभी ग्राहक", "Total Customers": "कुल ग्राहक",
    "Edit Profile": "प्रोफ़ाइल संपादित करें", "My Profile": "मेरी प्रोफ़ाइल", "Show Profile": "प्रोफ़ाइल देखें",
    "Date": "तारीख", "Time": "समय", "Item Name": "वस्तु का नाम", "Item ID": "वस्तु आईडी", "Item Id": "वस्तु आईडी",
    "Item Quantity": "वस्तु मात्रा", "Item Price": "वस्तु मूल्य", "Item Image": "वस्तु चित्र", "Item Details": "वस्तु विवरण",
    "Add Distributor": "वितरक जोड़ें", "Add Stock": "स्टॉक जोड़ें", "Add Item": "वस्तु जोड़ें", "Add": "जोड़ें",
    "View Applied Stock": "आवेदित स्टॉक देखें", "Stock Details": "स्टॉक विवरण", "Stock": "स्टॉक",
    "Apply For Stock": "स्टॉक के लिए आवेदन", "Check Available Stock": "उपलब्ध स्टॉक देखें", "Check Stock": "स्टॉक देखें",
    "Give Stock": "स्टॉक दें", "Requested Stock": "मांगा गया स्टॉक", "Approve Stock": "स्टॉक स्वीकृत करें", "Approve": "स्वीकृत करें",
    "Check Complains": "शिकायतें देखें", "Check Complaints": "शिकायतें देखें", "Complaints": "शिकायतें",
    "Add Complaint": "शिकायत दर्ज करें", "Complaint ID": "शिकायत आईडी", "Complaint Description": "शिकायत विवरण",
    "Admin": "व्यवस्थापक", "Admin Dashboard": "व्यवस्थापक डैशबोर्ड", "Analytics": "विश्लेषण",
    "Distributor": "वितरक", "Distributors Details": "वितरक विवरण", "Distributor Details": "वितरक विवरण",
    "Distributor Dashboard": "वितरक डैशबोर्ड", "Distributor Name": "वितरक का नाम", "Total Distributors": "कुल वितरक",
    "Suspend Distributor": "वितरक निलंबित करें", "Suspend": "निलंबित करें",
    "Assign Ration to All Distributors": "सभी वितरकों को राशन दें",
    "Customer Dashboard": "ग्राहक डैशबोर्ड", "Customer Details": "ग्राहक विवरण", "Customer Name": "ग्राहक का नाम",
    "View Statement": "विवरण देखें", "Statement": "विवरण", "Recent Transactions": "हाल के लेन-देन",
    "Recent Transections": "हाल के लेन-देन", "Transactions": "लेन-देन", "Transections": "लेन-देन",
    "Print Receipt": "रसीद प्रिंट करें", "Print": "प्रिंट", "Receipt": "रसीद", "Distributor Receipt": "वितरक रसीद",
    "PDS No": "पीडीएस नं.", "PDS No.": "पीडीएस नं.", "PDS Number": "पीडीएस नंबर",
    "Quantity": "मात्रा", "Total Quantity": "कुल मात्रा", "Price": "मूल्य", "Amount": "राशि",
    "Total": "कुल", "Total Amount": "कुल राशि", "Total Price in ₹": "कुल मूल्य ₹ में",
    "My Ration Card": "मेरा राशन कार्ड", "E-Ration Card": "ई-राशन कार्ड", "Ration Card No": "राशन कार्ड नं.",
    "Rationcard No.": "राशन कार्ड नं.", "Ration-Card No.": "राशन कार्ड नं.", "Rationcard Number": "राशन कार्ड नंबर",
    "Card Type": "कार्ड प्रकार", "All": "सभी", "Family Members": "परिवार के सदस्य", "Age": "आयु",
    "Name": "नाम", "First Name": "पहला नाम", "Middle Name": "मध्य नाम", "Last Name": "उपनाम", "User Name": "उपयोगकर्ता नाम",
    "Username": "उपयोगकर्ता नाम", "DOB": "जन्म तिथि", "Gender": "लिंग", "Male": "पुरुष", "Female": "महिला", "Other": "अन्य",
    "Pincode": "पिनकोड", "Address": "पता", "Shop Address": "दुकान का पता", "City": "शहर", "State": "राज्य",
    "Phone No": "फ़ोन नं.", "Phone Number": "फ़ोन नंबर", "Mobile No.": "मोबाइल नं.", "Email ID": "ईमेल आईडी",
    "Email-Id": "ईमेल आईडी", "Email-id": "ईमेल आईडी", "E-mail": "ईमेल", "Email": "ईमेल",
    "Aadhar Card No": "आधार कार्ड नं.", "Aadharcard No.": "आधार कार्ड नं.", "AadharCard No.": "आधार कार्ड नं.",
    "Password": "पासवर्ड", "Confirm Password": "पासवर्ड की पुष्टि करें", "Enter Password": "पासवर्ड दर्ज करें",
    "Enter Confirm Password": "पासवर्ड दोबारा दर्ज करें", "Forgot Password": "पासवर्ड भूल गए",
    "Forgot the password?": "पासवर्ड भूल गए?", "Select": "चुनें", "Submit": "जमा करें", "SUBMIT": "जमा करें",
    "Search": "खोजें", "Search...": "खोजें...", "Sign In": "साइन इन", "Log In": "लॉग इन", "Login Here": "यहाँ लॉग इन करें",
    "Log In Here": "यहाँ लॉग इन करें", "Login Page": "लॉगिन पेज", "Register Now": "अभी पंजीकरण करें",
    "Register Here": "यहाँ पंजीकरण करें", "Register Page": "पंजीकरण पेज", "Verify Account": "खाता सत्यापित करें",
    "Don't have an account?": "खाता नहीं है?", "Already have an account?": "पहले से खाता है?",
    "SR No": "क्र. सं.", "SR No.": "क्र. सं.", "Status": "स्थिति", "Delivered": "वितरित", "Pending": "लंबित",
    "Subject": "विषय", "Message": "संदेश", "Description": "विवरण", "Reason": "कारण", "Yes": "हाँ", "No": "नहीं",
    "Dark mode": "डार्क मोड", "Light mode": "लाइट मोड", "Hello": "नमस्ते", "Welcome to the E-Ration...": "ई-राशन में आपका स्वागत है...",
    "About Us": "हमारे बारे में", "Contact Us": "संपर्क करें", "Services": "सेवाएँ", "Ration": "राशन", "Let's Start": "शुरू करें",
    "Start now": "अभी शुरू करें", "View Features": "सुविधाएँ देखें", "E-Ration Key Features": "ई-राशन मुख्य सुविधाएँ",
    "Responsive": "रेस्पॉन्सिव", "Secure": "सुरक्षित", "Feedback": "प्रतिक्रिया", "Send Message": "संदेश भेजें",
    "Ration Help Line Number": "राशन हेल्पलाइन नंबर", "GET IN TOUCH WITH US": "हमसे संपर्क करें",
    "Revolutionary E-Ration Digital Platform solution.": "क्रांतिकारी ई-राशन डिजिटल प्लेटफ़ॉर्म समाधान।",
    "Ration Node": "राशन नोड", "Customer at node": "नोड पर ग्राहक", "Loading…": "लोड हो रहा है…",
    "Enroll fingerprint": "फिंगरप्रिंट दर्ज करें", "Enroll on node": "नोड पर दर्ज करें",
    "Choose The Date": "तारीख चुनें", "From": "से", "To": "तक", "Total Buy": "कुल खरीद", "Total Sell": "कुल बिक्री",
    "Total Sales": "कुल बिक्री", "Recent Sales": "हाल की बिक्री", "Sales": "बिक्री", "See All": "सभी देखें",
    "Applied ID": "आवेदन आईडी", "Applied Id": "आवेदन आईडी", "Recent Requests": "हाल के अनुरोध",
    "Select User Type": "उपयोगकर्ता प्रकार चुनें", "Gujarat": "गुजरात", "Surat": "सूरत", "Ahmedabad": "अहमदाबाद", "Rajkot": "राजकोट",
    "No Transactions in this duration!!": "इस अवधि में कोई लेन-देन नहीं!!", "No transcantions yet": "अभी तक कोई लेन-देन नहीं",
    "Enter First Name": "पहला नाम दर्ज करें", "Enter Middle Name": "मध्य नाम दर्ज करें", "Enter Last Name": "उपनाम दर्ज करें",
    "Enter Email ID": "ईमेल आईडी दर्ज करें", "Enter Phone No.": "फ़ोन नं. दर्ज करें", "Enter Aadharcard No.": "आधार नं. दर्ज करें",
    "Enter Rationcard No.": "राशन कार्ड नं. दर्ज करें", "Enter Ration Card No": "राशन कार्ड नं. दर्ज करें",
    "Enter Rationcard Number": "राशन कार्ड नंबर दर्ज करें", "Enter PDS No.": "पीडीएस नं. दर्ज करें", "Enter PDS No": "पीडीएस नं. दर्ज करें",
    "Enter PDS Number": "पीडीएस नंबर दर्ज करें", "Enter Pincode": "पिनकोड दर्ज करें", "Enter Address": "पता दर्ज करें",
    "Enter Username": "उपयोगकर्ता नाम दर्ज करें", "Enter Description": "विवरण दर्ज करें", "Enter Reason": "कारण दर्ज करें",
    "Enter Quantity..": "मात्रा दर्ज करें..", "Search By Item": "वस्तु से खोजें", "Search By Item Name": "वस्तु नाम से खोजें",
    "Wheat": "गेहूँ", "Rice": "चावल", "Sugar": "चीनी", "Dal": "दाल", "Oil": "तेल", "Bajara": "बाजरा"
  };
  const KEY = 'lang';
  const orig = new WeakMap();      // node -> original English
  let lang = 'en';
  try { lang = localStorage.getItem(KEY) || 'en'; } catch (e) {}

  function tr(text) {
    const m = text.match(/^(\s*)(.*?)(\s*[:*]?\s*)$/s);
    const hi = HI[m[2]];
    return hi ? m[1] + hi + m[3] : null;
  }
  function apply(root) {
    const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    for (let n; (n = w.nextNode());) {
      if (!orig.has(n)) { if (!tr(n.nodeValue)) continue; orig.set(n, n.nodeValue); }
      n.nodeValue = lang === 'hi' ? tr(orig.get(n)) : orig.get(n);
    }
    root.querySelectorAll && root.querySelectorAll('[placeholder], input[type=submit], input[type=button]').forEach(el => {
      const attr = el.hasAttribute('placeholder') ? 'placeholder' : 'value';
      if (!orig.has(el)) { if (!tr(el.getAttribute(attr))) return; orig.set(el, el.getAttribute(attr)); }
      el.setAttribute(attr, lang === 'hi' ? tr(orig.get(el)) : orig.get(el));
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:99999;padding:8px 14px;border:0;border-radius:20px;background:#0a2558;color:#fff;cursor:pointer;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,.3)';
    const label = () => btn.textContent = lang === 'hi' ? 'English' : 'हिंदी';
    btn.onclick = () => {
      lang = lang === 'hi' ? 'en' : 'hi';
      try { localStorage.setItem(KEY, lang); } catch (e) {}
      document.documentElement.lang = lang;
      apply(document.body); label();
    };
    label();
    document.body.appendChild(btn);
    document.documentElement.lang = lang;
    if (lang === 'hi') apply(document.body);
    // Pages that redraw by AJAX (e.g. distributor/node.php) get translated too.
    new MutationObserver(ms => { if (lang === 'hi') ms.forEach(m => m.addedNodes.forEach(n => n.nodeType === 1 && n !== btn && apply(n))); })
      .observe(document.body, { childList: true, subtree: true });
  });
})();
