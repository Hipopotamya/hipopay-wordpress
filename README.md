# 💳 HipoPAY Payment Gateway for WordPress & WooCommerce

<p align="center">
  <img src="https://www.hipopotamya.com/img/logos/hipopotamya-white-logo.png" alt="HipoPotamya Logo" width="400"/>
</p>

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-Tested-purple.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-8a8cb9.svg)
[![Download](https://img.shields.io/badge/Download-Latest%20Release-brightgreen)](https://github.com/Hipopotamya/hipopay-wordpress/releases/latest)

Bu depo, HipoPAY ödeme altyapısını WordPress ve WooCommerce tabanlı projelerinizde kullanmanızı sağlayan resmi, gelişmiş ödeme eklentisini barındırmaktadır.

HipoPAY, güçlü ve güvenli altyapısıyla kredi kartı işlemlerini hızlıca gerçekleştirmenizi sağlar.

---

## 📦 Download

👉 **En güncel sürümü indir:**
https://github.com/Hipopotamya/hipopay-wordpress/releases/latest

📌 **Not:** WordPress’e yüklemek için yalnızca release `hipopay-payment-gateway.zip` dosyasını kullanın.

---

## 🚀 Öne Çıkan Özellikler

* **WooCommerce Uyumlu:** WooCommerce siparişleriyle tam entegrasyon (Sipariş oluşturma, IPN ile otomatik sipariş durumu güncelleme).
* **Sayfa Oluşturucular (Page Builders):** Eklenti, **Elementor** ve **WPBakery (Visual Composer)** için yerleşik widget'lar (bileşenler) sunar. Sürükle bırak ile kolayca ödeme formu tasarlayın.
* **Bağımsız Formlar (Shortcodes):** WooCommerce'e ihtiyaç duymadan, yalnızca `[hipopay_form]` veya `[hipopay_donate]` kısa kodları ile oyun E-Pin satışları, bağışlar veya VIP üyelikler için formlar oluşturabilirsiniz.
* **Esnek Komisyon:** Komisyon ücretinin kim tarafından ödeneceğini esnek bir şablonda belirleme imkanı (Örn: Satıcı öder, alıcı öder).
* **Kapsamlı Yönetim Paneli:** İşlem grafikleri, net kazanım özetleri ve tüm işlemlerin takip edilebildiği gelişmiş WordPress Admin arayüzü.
* **Çoklu Dil (i18n):** Türkçe, İngilizce ve Arapça desteği.

---

## 🛠 Kurulum ve Kullanım

### 1. WordPress'e Eklenti Yükleme

* Releases sayfasından `.zip` dosyasını indirin
* WordPress → **Eklentiler → Yeni Ekle → Eklenti Yükle**
* ZIP dosyasını yükleyin
* Eklentiyi etkinleştirin

---

### 2. API Bağlantılarını Gerçekleştirme

* **HipoPAY → Ayarlar** sekmesini açın
* API Key ve API Secret bilgilerinizi girin
* “Bağlantıyı Test Et” ile kontrol edin

---

### 3. Webhook (Callback) Tanımlaması [KRİTİK ADIM]

- WordPress admin menüsünde sol tarafta beliren **HipoPAY -> Ayarlar** sekmesini açın.
- HipoPAY [Satıcı panelinizden](https://www.hipopotamya.com/merchants/stores) edindiğiniz **API Key** ve **API Secret** bilgilerini ilgili alanlara yapıştırın.
- "Bağlantıyı Test Et" özelliğini kullanarak API'nin sorunsuz çalıştığından emin olun.

---

## 💻 Entegrasyon Yöntemleri

### Yöntem A: WooCommerce

WooCommerce kullanıyorsanız ekstra bir işleme gerek yoktur. Ayarlar kısmından WooCommerce kutucuğunu aktif hale getirip API bilgilerinizi kaydettiğinizde, müşterileriniz ödeme sayfasında HipoPAY seçeneğini görecektir.

---

### Yöntem B: Kısa Kod (Shortcode) Kullanımı
WooCommerce kullanmayan sayfalar için aşağıdaki kodları herhangi bir sayfa veya yazı içerisine ekleyebilirsiniz:

**Sabit Tutarlı Ödeme**
```html
[hipopay_form price="150" product_name="VIP Üyelik Paketi" button_text="Hemen Satın Al"]
```

**Kullanıcının Tutar Girdiği Bağış Kutusu**
```html
[hipopay_donate product_name="Topluluk Bağışı" button_text="Destek Ol"]
```

---

### Yöntem C: Elementor / WPBakery
* Sayfanızı Elementor veya WPBakery ile düzenlerken, sol kısımdaki bileşenler menüsünde **HipoPAY Ödeme Formu** araması yapın.
* Modülü sürükleyip sayfaya bıraktıktan sonra ürün adını, fiyatını ve buton tasarımını görsel olarak ayarlayabilirsiniz.


---

## 🐛 Sorun Giderme (Troubleshooting)

- **Para Birimi Hatası:** HipoPAY sadece Türk Lirası (**TRY**) işlemlerini kabul eder. WooCommerce para birimi Dolar veya Euro ise ödeme ekranı görüntülenmez.
- **Siparişler "Bekliyor" da kalıyor:** Webhook URL'sini panelinize tanımlamadığınız için HipoPAY, WordPress sitenize başarılı sonucunu bildiremiyordur. Ayarlarınızı kontrol edin.
- **Test (Sandbox) Modu:** Eğer deneme satın alımı yapıyorsanız Gelişmiş Ayarlar bölümünden Test Modunu açmayı unutmayın.

---

## 📝 Lisans

Bu eklenti WordPress prensiplerine uygun açık kaynaklı bir yapıyla geliştirilmiştir (**GPLv2**). HipoPAY altyapısı hakkındaki teknik sorularınız için **[HipoPAY Destek Ekibi](https://www.hipopotamya.com/)** ile iletişime geçebilirsiniz.


---

## 🌐 İletişim

👉 https://www.hipopotamya.com/
