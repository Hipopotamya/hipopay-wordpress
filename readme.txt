=== HipoPAY Payment Gateway ===
Contributors: hipopotamya
Tags: hipopay, payment gateway, woocommerce, payment, sanal pos, kredi kartı
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

HipoPAY güçlü, güvenli ve hızlı ödeme altyapısı ile WordPress ve WooCommerce sitelerinizden kolayca ödeme almanızı sağlar.

== Description ==

**HipoPAY Payment Gateway**, WordPress web siteniz üzerinden kredi kartı veya banka kartı ile güvenle ödeme almanızı sağlayan resmi Hipopotamya.com entegrasyonudur.

İster kapsamlı bir e-ticaret sitesi işletiyor olun (WooCommerce), ister sadece bağış toplamak veya tekil dijital ürün (E-Pin, VIP üyelik vs.) satmak isteyin; HipoPAY eklentisi ihtiyacınız olan tüm araçları sunar. Eklenti, WooCommerce gereksinimi olmadan **Elementor**, **WPBakery** ve **Shortcode (Kısa kod)** destekleriyle her türden WordPress sayfasına entegre edilebilir.

= Öne Çıkan Özellikler =
* **WooCommerce Uyumu:** WooCommerce ödeme yöntemlerine tam entegre çalışır. Müşterileriniz ödeme adımında HipoPAY arayüzü ile karşılaşır.
* **Bağımsız Ödeme Formları (Shortcode):** `[hipopay_form]` ve `[hipopay_donate]` kısa kodları ile WooCommerce kurmaya gerek kalmadan sabit tutarlı veya kullanıcının belirlediği (bağış) tutarlarda ödeme alabilirsiniz.
* **Sayfa Oluşturucu Desteği (Page Builders):** Elementor ve WPBakery (Visual Composer) için özel olarak hazırlanmış bileşenlerle (widget) sürükle-bırak yöntemiyle sayfanıza ödeme formu ekleyebilirsiniz.
* **Kapsamlı Yönetim Paneli:** WordPress admin paneli üzerinden tüm ödeme işlemlerinizi, başarılı/başarısız durumlarını ve gelir istatistiklerinizi grafiklerle takip edebilirsiniz.
* **Esnek Komisyon Yapısı:** Komisyon ücretinin satıcı veya alıcı tarafından (Tip 1, Tip 2, Tip 3) karşılanmasını seçebilirsiniz.
* **Gelişmiş Çoklu Dil (i18n):** Türkçe (tr_TR), İngilizce (en_US) ve Arapça (ar) dilleri yerleşik olarak desteklenir.
* **Güvenli Webhook (IPN):** Ödeme sonuçları anında WordPress / WooCommerce sisteminize iletilerek sipariş durumları otomatik güncellenir.

== Installation ==

= Yükleme ve Etkinleştirme =
1. Eklenti .zip dosyasını indirin.
2. WordPress yönetim panelinize giriş yapın ve **Eklentiler > Yeni Ekle** menüsüne gidin.
3. Ekranın üst kısmındaki **Eklenti Yükle** butonuna tıklayın, indirdiğiniz .zip dosyasını seçip yükleyin.
4. Yükleme tamamlandıktan sonra eklentiyi **Etkinleştirin**.

= HipoPAY API Ayarları =
1. HipoPAY Müşteri Panelinize giriş yapın ve Mağazalarım bölümünden API anahtarlarınızı (Merchant API Key ve Secret) kopyalayın.
2. WordPress panelinizde sol menüde oluşan **HipoPAY** sekmesinden **Ayarlar** sayfasına gidin.
3. API anahtarlarınızı bu sayfaya yapıştırın ve bağlantıyı test edin.
4. **ÖNEMLİ:** Ayarlar sayfasında size verilen **Webhook (Callback) URL** adresini, HipoPAY panelinizdeki ilgili alana mutlaka yapıştırın. Bu işlem, ödemelerin sitenize anında yansımasını sağlar.

= WooCommerce Entegrasyonu =
1. **WooCommerce > Ayarlar > Ödemeler** sekmesine gidin.
2. HipoPAY yöntemini aktif hale getirin ve "Yönet" butonuna tıklayın.
3. Ekranda istenen WooCommerce API bilgilerini ve diğer ödeme sayfası tasarım/komisyon seçeneklerini doldurun.

== Frequently Asked Questions ==

= Eklentiyi kullanmak için WooCommerce kurmak zorunlu mu? =
Hayır, eklentiyi sadece shortcode (kısa kod) özelliklerini kullanarak, veya Elementor/WPBakery bileşenleriyle WooCommerce olmadan da kullanabilirsiniz.

= Hangi para birimlerini destekliyor? =
Şu an için HipoPAY altyapısı yalnızca Türk Lirası (TRY) üzerinden işlemleri desteklemektedir. WooCommerce para biriminizin TRY olarak ayarlandığından emin olun.

= Webhook (IPN/Callback) URL yapılandırmak neden gerekli? =
Müşteriniz HipoPAY ortak ödeme sayfasına yönlendirilip ödemeyi tamamladığında, sonucun (Başarılı/Başarısız) WordPress sisteminize (veya WooCommerce siparişine) güvenle bildirilmesi Webhook üzerinden yapılır.

== Screenshots ==

1. HipoPAY Yönetim Paneli - Genel Bakış ve İstatistikler
2. HipoPAY Yönetim Paneli - İşlemler (Transactions) Listesi
3. HipoPAY Kısa Kod / Bağımsız Form Ayarları
4. WooCommerce Ödeme Ayarları

== Changelog ==

= 1.0.0 =
* İlk sürüm yayınlandı.
* WooCommerce ödeme geçidi eklendi.
* Shortcode, Elementor ve WPBakery entegrasyonları eklendi.
* Gelişmiş işlem listeleme ve istatistik paneli eklendi.
* TR, EN, AR dil destekleri tamamlandı.
