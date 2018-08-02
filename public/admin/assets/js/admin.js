/**
 * 后台通用js文件 存放通用功能调用
 * Created by xzp on 2018/8/1.
 */
_Admin = {

    /**
     * 后台弹出提示信息
     * 需要包含 messenger 及相应 主题样式
     * @param msg 提示信息
     * @param type error
     * @param second 秒
     * @param id 元素id  避免重复显示
     * @param options 其他设置
     */
    tips: function (msg, type, second, id, options) {
        if (typeof Messenger === 'undefined') {
            alert(msg);
        } else {
            if (typeof(options) == 'undefined')
                options = {extraClasses: 'messenger-fixed messenger-on-top', theme: 'block'};
            if (typeof(type) == 'undefined') type = 'success';
            if (typeof(second) == 'undefined') {
                if (type == 'success') second = 3;
                else second = 5;
            }
            if (typeof(id) == 'undefind') id = '';
            Messenger.options = options;
            Messenger().post({message: msg, type: type, hideAfter: second, hideOnNavigate: true, id: id});
        }
    },

    // ajax请求
    ajax: function (url, data, success_fun, type, error_fun, dataType) {
        var _this = this;
        if (url == '') {
            _this.tips('ajax 请求地址不可为空');
            return;
        }
        if (success_fun == '') {
            _this.tips('ajax 处理函数不存在');
            return;
        }
        if (typeof(type) == 'undefined') type = data ? 'POST' : 'GET';
        if (dataType == '' || typeof(dataType) == 'undefined') dataType = 'json';
        if (typeof(error_fun) == 'undefined') error_fun = function (jqXHR, textStatus, errorThrown) {
            _this.tips(errorThrown, 'error');
        };
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({type: type, url: url, data: data, dataType: dataType, success: success_fun, error: error_fun});
    },

    // 清空表单
    clearForm: function (formSelector) {
        $(':input', formSelector).not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected');
    },

    // 列表全选处理
    checkbox: {
        // 初始化全选 取消绑定事件
        init: function (allSelector, singleSelector) {
            $(allSelector).click(function (event) {
                if ($(this).prop('checked')) { // 全选操作
                    $(singleSelector).prop('checked', true);
                } else { // 取消全选
                    $(singleSelector).prop('checked', false);
                }
            });
        },

        // 获取全选值
        values: function (singleSelector) {
            var checkeItems = $(singleSelector);
            var ret = [];
            for (var index = 0; index < checkeItems.length; ++index)
                ret.push($(checkeItems[index]).val());
            return ret;
        }
    },

    /**
     * 取反处理
     * @param selector
     * @param el1
     * @param el2
     * @param url
     * @param callback
     */
    setToggle: function (selector, el1, el2, url, callback) {
        $(selector).click(function (e) {
            var _this = this;
            var current = $(_this).html();
            var change = $(current).attr('data-value') == $(el1).attr('data-value') ? el2 : el1;
            $(this).html(change);
            var push_data = {
                id: $(_this).attr('data-id'),
                field: $(_this).attr('data-field'),
                value: $(change).attr('data-value')
            };
            _Admin.ajax(url, push_data, function (data) {
                if (data.status == 0) {
                    if (typeof callback != 'undefined' && callback)
                        callback(this, change);
                    _Admin.tips('操作成功', 'success', 1);
                } else {
                    _Admin.tips('操作失败: ' + data.msg, 'error', 5);
                }
            }, 'POST');
        });
    },

    /**
     * 将某个文本转换输入框 并 失去焦点 点击元素处理
     * @param selector  选择器
     * @param url 回调
     * @param width 回调
     * @param callback 回调函数
     */
    setInput: function (selector, url, width, callback) {
        if (typeof(width) == 'undefined') width = '50';
        $(selector).undelegate('input', 'blur').delegate('input', 'blur', function (e) {
            var value = $(this).val();
            var obj = $(this).parent();
            obj.text(value);

            var old_value = $(this).attr('data-old');
            if (old_value != value) {
                var push_data = {id: obj.attr('data-id'), field: obj.attr('data-field'), value: value};
                _Admin.ajax(url, push_data, function (data) {
                    if (data.status == 0) {
                        if (typeof callback != 'undefined' && callback)
                            callback(obj, value);
                        _Admin.tips('操作成功', 'success', 1);
                    } else {
                        _Admin.tips('操作失败: ' + data.msg, 'error', 5);
                    }
                }, 'POST');
            }
        });
        $(selector).click(function (e) {
            if ($(this).find('input').length > 0) return;
            var data = _Admin.trim($(this).text());
            $(this).html('<input type="text" data-old="' + data + '" style="max-width:' + width + 'px;" value="' + data + '" />');
            $(this).find('input').focus();
        });
    },

    /**
     * 去掉字符串首位空格
     *
     * @param str
     * @returns {XML|string|void|*}
     */
    trim: function (str) {
        return str.replace(/^\s+|\s+$/g, '');
    },

    /**
     * 定时器显示时间
     * @param selector
     */
    showTime: function (selector) {
        tick = function (selector) {
            var now = new Date();
            var month = now.getMonth() + 1;
            var day = now.getDate();
            var hour = now.getHours();
            var minute = now.getMinutes();
            var second = now.getSeconds();
            var dateStr = now.getFullYear() + "-" + ( month > 9 ? month : '0' + month) + "-" + (day > 9 ? day : '0' + day) + " "
                + (hour > 9 ? hour : '0' + hour ) + ":" + (minute > 9 ? minute : '0' + minute) + ":" + (second > 9 ? second : '0' + second);
            $(selector).text(dateStr);
        };
        window.setInterval('tick("' + selector + '");', 1000);
    },
    /**
     * 自定义菜单处理
     */
    customMenus: {
        selector: '',
        uniqueKey: '',
        init: function(key, selector, addSelector){
            var _this = this;
            _this.selector = selector;
            _this.uniqueKey= key;
            _this.fillCustomMenus();

            // 删除 自定义菜单
            $(selector).undelegate('span','click').delegate('span','click',function(e){
                e.preventDefault();
                e.stopPropagation();
                var action_url = $(this).parent().attr('href');
                $(this).parent().remove();
                _this.dealCustomMenus(action_url,null);
            });

            // 添加自定义菜单
            $(addSelector).click(function(){
                if(localStorage){
                    var action_name = $(this).attr('data-name');
                    var action_url  = $(this).attr('data-url');
                    _this.dealCustomMenus(action_url,action_name);
                    _this.fillCustomMenus();
                    _Admin.tips('已成功加入常用菜单列表','success',3,'customMenusId');
                }
            });
        },

        /** 填充左侧快捷菜单 **/
        fillCustomMenus: function(){
            var customMenusJson = this.dealCustomMenus(null,null);
            if(customMenusJson && !$.isEmptyObject(customMenusJson)){
                var html = '<a class="list-group-item  list-group-item-info text-center disabled">常用菜单列表</a>';
                for(var index in customMenusJson){
                    html += '<a class="list-group-item " href="'+index+'">'+customMenusJson[index]+'<span class="glyphicon glyphicon-remove pull-right"></span></a>';
                }
                $(this.selector).html(html);
            }
        },

        dealCustomMenus: function(key,value){
            var customMenus = localStorage.getItem('customMenus') || '{}';
            var username    = this.uniqueKey;
            var customMenusJson = JSON.parse(customMenus);
            if(key && value){
                if(!customMenusJson[username]) customMenusJson[username] = {};
                customMenusJson[username][key] = value;
                localStorage.setItem('customMenus',JSON.stringify(customMenusJson));
                return true;
            }else if(key && value===null){
                delete customMenusJson[username][key];
                localStorage.setItem('customMenus',JSON.stringify(customMenusJson));
                return true;
            }else if(key === null && value===null){
                return customMenusJson[username];
            }
        }
    }
};