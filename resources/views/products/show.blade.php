@extends('layouts.app')
@section('title', $product->title)

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-body product-info">
                <div class="row">
                    <div class="col-5">
                        <!-- <img class="cover" src="{{ URL::asset('/upload/'.$product->image[0]) }}" alt=""> -->
                        <div id="bigImg" class="active">
                            <img src="{{ URL::asset('/upload/'.$product->image[0]) }}" alt="" width="100%">
                        </div>
                        <div class="slider-1">
                            @foreach($product->image as $image)
                                <div class="li"><img src="{{ URL::asset('/upload/'.$image) }}" alt=""></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-7">
                        <div class="title">{{ $product->title }}</div>
                        <div class="price"><label>价格</label><em>￥</em><span>{{ $product->price }}</span></div>
                        <div class="sales_and_reviews">
                            <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
                            <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
                            <div class="rating" title="评分 {{ $product->rating }}">评分 <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
                        </div>
                        <div class="skus">
                            <label>选择</label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                @foreach($product->skus as $sku)
                                <label class="btn sku-btn" data-price="{{ $sku->price }}" data-stock="{{ $sku->stock }}" data-toggle="tooltip" title="{{ $sku->description }}" data-placement="bottom">
                                    <input type="radio" name="skus" autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="cart_amount"><label>数量</label><input type="text" class="form-control form-control-sm" value="1"><span>件</span><span class="stock"></span></div>
                        <div class="buttons">
                            @if($favored)
                                <button class="btn btn-danger btn-disfavor">取消收藏</button>
                            @else
                                <button class="btn btn-success btn-favor">❤ 收藏</button>
                            @endif
                            <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
                        </div>
                    </div>
                </div>
                <div class="product-detail">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab" aria-selected="true">商品详情</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab" aria-selected="false">用户评价</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="product-detail-tab"> 
                            <!-- <h6>产品参数:</h6>
                            <ul class="description">
                                @foreach($description as $key => $value)
                                <li>{!! $value !!}</li>
                                @endforeach
                            </ul> -->

                            <!-- 商品属性开始 -->
                            <div class="properties-list">
                                <div class="properties-list-title">产品参数：</div>
                                <ul class="properties-list-body">
                                @foreach($product->grouped_properties as $name => $values)
                                    <li>{{ $name }}：{{ join(' ', $values) }}</li>
                                @endforeach
                                </ul>
                            </div>
                            <!-- 商品属性结束 -->
                            <div class="product-description">
                                {!! $product->description !!}
                            </div>

                            <div>
                                @if($product->images)
                                    @foreach($product->images as $img)
                                    <img class="cover-min" src="{{ URL::asset('/upload/'.$img) }}" alt="">
                                    @endforeach 
                                @endif
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                            <table class="tabel table-bordered table-striped" style="width:100%;font-size:15px;">
                                <thead>
                                    <tr>
                                        <td>用户</td>
                                        <td>商品</td>
                                        <td>评分</td>
                                        <td>评价</td>
                                        <td>时间</td>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($reviews as $review)
                                <tr>
                                    <td>{{ $review->order->user->name }}</td>
                                    <td>{{ $review->productSku->title }}</td>
                                    <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                    <td>{{ $review->review }}</td>
                                    <td>{{ $review->reviewed_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scriptsAfterJs')
<script>
    $(document).ready(function() {
        // 这个属性来启用 Bootstrap 的工具提示来美化样式
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover'
        });

        $('.sku-btn').click(function() {
            $('.product-info .price span').text($(this).data('price'));
            $('.product-info .stock').text('库存：' + $(this).data('stock') + '件');
        });

        // 监听收藏按钮的点击事件
        $('.btn-favor').click(function () {
            axios.post('{{ route('products.favor', ['product' => $product->id]) }}').then(function () {
                swal('收藏成功', '', 'success').then(function () {
                    location.reload();
                });
            }, function(error) {
                // 返回码 401 代表没登录
                if (error.response && error.response.status === 401) {
                    swal('请先登录', '', 'error');
                } else if (error.response && (error.response.data.msg || error.response.data.message)) {
                    // 其他有 msg 或者 message 字段的情况，将 msg 提示给用户
                    swal(error.response.data.msg ? error.response.data.msg : error.response.data.message, '', 'error');
                }  else {
                    swal('系统错误', '', 'error');
                }
            });
        });

        $('.btn-disfavor').click(function () {
            axios.delete('{{ route('products.disfavor', ['product' => $product->id]) }}').then(function () {
                swal('取消收藏成功', '', 'success').then(function () {
                    location.reload();
                });
            });
        });

        // 加入购物车按钮点击事件
        $('.btn-add-to-cart').click(function () {
            // 请求加入购物车接口
            axios.post('{{ route('cart.add') }}', {
                sku_id: $('label.active input[name=skus]').val(),
                amount: $('.cart_amount input').val(),
            }).then(function () {
                swal('加入购物车成功', '', 'success').then(function () {
                    var count = parseInt($('.badge-success').text());
                    if (isNaN(count)){ 
                        $('.badge-success').text('1');
                    } else {
                        count = count + 1;
                        $('.badge-success').text(count);
                    }
                });
            }, function (error) { // 请求失败执行此回调
                if (error.response.status === 401) {
                    swal('请先登录', '', 'error');
                } else if (error.response.status === 422) {
                    // http 状态码为 422 代表用户输入校验失败
                    var html = '<div>';
                    _.each(error.response.data.errors, function (errors) {
                        _.each(errors, function (error) {
                            html += error+'<br>';
                        })
                    });
                    html += '</div>';

                    swal({content: $(html)[0], icon: 'error'})
                } else {
                    swal('系统错误', '', 'error');
                }
            })
        });

        var bigImg = document.getElementById('bigImg');
        // 该选择器类似于css选择器
        var ul = document.querySelector('div.slider-1');
        var lis = document.querySelectorAll('div.li');

        var allPage = lis.length;
        var page = 0;

        var timer = null;

        for (var i = 0; i < allPage; i++) {
            lis[i].index = i;

            lis[i].onclick = function () {
                page = this.index;

                slider();
            }
        }

        function slider() {
            bigImg.style.animation = "action 0.9s";

            bigImg.getElementsByTagName('img')[0].src = lis[page].getElementsByTagName('img')[0].src;

            setTimeout(function () {
                bigImg.style.animation = "";
            }, 900);

            for (var i = 0; i < lis.length; i++) {
                lis[i].style.opacity = 0.7;
                if (i == page) {
                    lis[i].style.opacity = 1;
                }
            }
        }

        ul.onmouseover = function () {
            clearInterval(timer);
            timer = null;
        }

        ul.onmouseout = function () {
            lunbo();
        }

        function lunbo() {
            timer = setInterval(function () {
                page++;
                if (page == allPage) {
                    page = 0;
                }
                slider();
            }, 1500);
        }

        lunbo();
    });
</script>
@endsection