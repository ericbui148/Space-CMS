<!-- Breadcrumbs -->
<section class="breadcrumbs-custom-inset">
<div class="breadcrumbs-custom context-dark">
  <div class="container">
    <h2 class="breadcrumbs-custom-title"><?php __i18n('contact');?></h2>
    <ul class="breadcrumbs-custom-path">
      <li><a href="/"><?php __i18n('home')?></a></li>
      <li class="active"><?php __i18n('contact')?></li>
    </ul>
  </div>
  <div class="box-position" style="background-image: url(public/asago/assets/images/bg-breadcrumbs.jpg);"></div>
</div>
</section>
<!-- RD Google Map-->
	<style>iframe {width:100%;height:100%;}</style>
<br/>
  <section class="section">
   <div class="container">
      <div class="mapouter"><div class="gmap_canvas"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.184854555511!2d106.74850861525736!3d10.797149792307493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175267279536c2d%3A0x2365910eb0f1b699!2sPetroVietnam%20Landmark!5e0!3m2!1sen!2s!4v1600221767080!5m2!1sen!2s" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe><a href="https://www.whatismyip-address.com/divi-discount/"></a></div><style>.mapouter{position:relative;text-align:right;height:343px;width:1080px;}.gmap_canvas {overflow:hidden;background:none!important;height:343px;width:1080px;}</style></div>
   </div>
  </section>

  <!-- Contact information-->
  <section class="section section-sm section-first bg-default">
    <div class="container">
      <div class="row row-30 justify-content-center">
        <div class="col-sm-8 col-md-6 col-lg-4">
          <article class="box-contacts">
            <div class="box-contacts-body">
              <div class="box-contacts-icon fl-bigmug-line-cellphone55"></div>
              <div class="box-contacts-decor"></div>
              <p class="box-contacts-link"><?php $controller->renderWidgetByName('contact_phone');?></p>
            </div>
          </article>
        </div>
        <div class="col-sm-8 col-md-6 col-lg-4">
          <article class="box-contacts">
            <div class="box-contacts-body">
              <div class="box-contacts-icon fl-bigmug-line-up104"></div>
              <div class="box-contacts-decor"></div>
              <p class="box-contacts-link"><?php $controller->renderWidgetByName('contact_address');?></p>
            </div>
          </article>
        </div>
        <div class="col-sm-8 col-md-6 col-lg-4">
          <article class="box-contacts">
            <div class="box-contacts-body">
              <div class="box-contacts-icon fl-bigmug-line-chat55"></div>
              <div class="box-contacts-decor"></div>
              <p class="box-contacts-link"><?php $controller->renderWidgetByName('contact_email');?></p>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Form-->
  <section class="section section-sm section-last bg-default text-left">
    <div class="container">
      <article class="title-classic">
        <div class="title-classic-title">
          <h3>Get in touch</h3>
        </div>
      </article>
      <form class="rd-form rd-form-variant-2 rd-mailform" data-form-output="form-output-global" data-form-type="contact" method="post" action="bat/rd-mailform.php">
        <div class="row row-14 gutters-14">
          <div class="col-md-4">
            <div class="form-wrap">
              <input class="form-input" id="contact-your-name-2" type="text" name="name" data-constraints="@Required">
              <label class="form-label" for="contact-your-name-2">Your Name</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-wrap">
              <input class="form-input" id="contact-email-2" type="email" name="email" data-constraints="@Email @Required">
              <label class="form-label" for="contact-email-2">E-mail</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-wrap">
              <input class="form-input" id="contact-phone-2" type="text" name="phone" data-constraints="@Numeric">
              <label class="form-label" for="contact-phone-2">Phone</label>
            </div>
          </div>
          <div class="col-12">
            <div class="form-wrap">
              <label class="form-label" for="contact-message-2">Message</label>
              <textarea class="form-input textarea-lg" id="contact-message-2" name="message" data-constraints="@Required"></textarea>
            </div>
          </div>
        </div>
        <button class="button button-secondary button-pipaluk" type="submit">Send Message</button>
      </form>
    </div>
  </section>