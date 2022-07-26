{use class="frontend\design\Info"}





<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="reviews">
      <div class="heading-3">{$smarty.const.REVIEWS}</div>

      <div class="reviews-list">
        <div class="item">
          <div class="date">Tuesday 27 September, 2016</div>
          <div class="review">kusheriges hitse kusheriges hitse kusheriges hitse...</div>
          <div class="name">Lotte Van Dijk <span class="rating-3"></span></div>
        </div>
        <div class="item">
          <div class="date">Tuesday 27 September, 2016</div>
          <div class="review">i bought five of these saturday mourning at a church...</div>
          <div class="name">Kate Kelly <span class="rating-4"></span></div>
        </div>
        <div class="item">
          <div class="date">Tuesday 27 September, 2016</div>
          <div class="review">A previous reviewer stated that his 3 main requirements...</div>
          <div class="name">Sara Cox <span class="rating-5"></span></div>
        </div>
      </div>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>

    <div class="edit-reviews edit-element-2"{Info::dataClass('.reviews')}>
      <div class="heading-3 edit-element-2"{Info::dataClass('.reviews .heading-3')}>{$smarty.const.REVIEWS}</div>

      <div class="edit-reviews-list edit-element-2"{Info::dataClass('.reviews-list')}>
        <div class="item edit-element-1"{Info::dataClass('.reviews-list .item')}>
          <div class="edit-date edit-element-1"{Info::dataClass('.reviews-list .date')}>Tuesday 27 September, 2016</div>
          <div class="edit-review edit-element-1"{Info::dataClass('.reviews-list .review')}>kusheriges hitse kusheriges hitse kusheriges hitse...</div>
          <div class="edit-name edit-element-1"{Info::dataClass('.reviews-list .name')}>Lotte Van Dijk <div class="rating-3 edit-element-1"{Info::dataClass('.reviews-list span')}></div></div>
        </div>
        <div class="item edit-element-1"{Info::dataClass('.reviews-list .item')}>
          <div class="edit-date edit-element-1"{Info::dataClass('.reviews-list .date')}>Tuesday 27 September, 2016</div>
          <div class="edit-review edit-element-1"{Info::dataClass('.reviews-list .review')}>i bought five of these saturday mourning at a church...</div>
          <div class="edit-name edit-element-1"{Info::dataClass('.reviews-list .name')}>Kate Kelly <div class="rating-4 edit-element-1"{Info::dataClass('.reviews-list span')}></div></div>
        </div>
        <div class="item edit-element-1"{Info::dataClass('.reviews-list .item')}>
          <div class="edit-date edit-element-1"{Info::dataClass('.reviews-list .date')}>Tuesday 27 September, 2016</div>
          <div class="edit-review edit-element-1"{Info::dataClass('.reviews-list .review')}>A previous reviewer stated that his 3 main requirements...</div>
          <div class="edit-name edit-element-1"{Info::dataClass('.reviews-list .name')}>Sara Cox <div class="rating-5 edit-element-1"{Info::dataClass('.reviews-list span')}></div></div>
        </div>
      </div>
    </div>


  </div>

</div>

