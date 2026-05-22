# 📬 Contact Me Form

A clean, responsive contact form built with HTML, CSS, and JavaScript — featuring real-time validation and email delivery powered by **EmailJS** (no server required).

---

## 🌐 Live Demo

> Hosted via GitHub Pages:  
> **[https://komallbarhate.github.io/contact-me-form](https://komallbarhate.github.io/contact-me-form)**

---

## ✨ Features

- 📋 **Contact Form** with Name, Email, Subject, and Message fields
- ✅ **Real-time Validation** — instant feedback as you type
- 📧 **EmailJS Integration** — sends messages directly to Gmail, no backend needed
- 📱 **Fully Responsive** — works on mobile, tablet, and desktop
- 🎨 **Warm Light Theme** — clean, simple, and friendly design
- ♿ **Accessible** — ARIA labels, semantic HTML, keyboard-friendly
- 🔗 **Social Links** — X (Twitter), LinkedIn, Instagram

---

## 📁 Project Structure

```
contact-me-form/
│
├── index.html        # Main HTML structure & form
├── style.css         # Styling (light warm theme)
├── script.js         # Validation + EmailJS submission
├── submit_form.php   # PHP fallback (for server-based hosting)
└── README.md         # Project documentation
```

---

## 🚀 Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/komallbarhate/contact-me-form.git
cd contact-me-form
```

### 2. Open locally

Just open `index.html` in your browser — no build step needed.

### 3. Activate email sending (EmailJS)

To receive emails at your Gmail when someone submits the form:

1. Sign up for free at [https://www.emailjs.com](https://www.emailjs.com)
2. Connect your Gmail account under **Email Services**
3. Create an **Email Template** with these variables:
   ```
   {{from_name}}, {{from_email}}, {{subject}}, {{message}}
   ```
4. Open `script.js` and fill in your credentials:

```js
const EMAILJS_PUBLIC_KEY  = 'YOUR_PUBLIC_KEY';
const EMAILJS_SERVICE_ID  = 'YOUR_SERVICE_ID';
const EMAILJS_TEMPLATE_ID = 'YOUR_TEMPLATE_ID';
```

---

## 🛠️ Built With

| Technology | Purpose |
|---|---|
| HTML5 | Form structure & semantics |
| CSS3 | Styling, layout, animations |
| JavaScript (ES6+) | Validation & EmailJS integration |
| [EmailJS](https://www.emailjs.com) | Client-side email delivery |
| Google Fonts (Inter) | Typography |

---

## 📬 Contact

**Komal Barhate**

[![Gmail](https://img.shields.io/badge/Gmail-barhate.komal12@gmail.com-red?style=flat&logo=gmail)](mailto:barhate.komal12@gmail.com)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Komal%20Barhate-blue?style=flat&logo=linkedin)](https://www.linkedin.com/in/komal-barhate-649108338)
[![Instagram](https://img.shields.io/badge/Instagram-@komallbarhate-E4405F?style=flat&logo=instagram)](https://www.instagram.com/komallbarhate)
[![X](https://img.shields.io/badge/X-@fairdopeee-black?style=flat&logo=x)](https://x.com/fairdopeee)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).
