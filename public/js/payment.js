class PaymentHandler {
    constructor() {
        this.queryId = null;
        this.initEventListeners();
    }

    initEventListeners() {
        document.getElementById('authCodeForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleAuthCodePayment();
        });

        document.getElementById('wechatPayBtn').addEventListener('click', () => {
            this.handleWechatPayment();
        });

        document.getElementById('alipayBtn').addEventListener('click', () => {
            this.handleAlipayPayment();
        });
    }

    async handleAuthCodePayment() {
        const authCode = document.getElementById('authCode').value;
        try {
            const response = await axios.post(`/payment/auth-code/${this.queryId}`, {
                auth_code: authCode
            });
            
            if (response.data.success) {
                this.showReport(response.data.report);
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    async handleWechatPayment() {
        try {
            const response = await axios.post(`/payment/wechat/${this.queryId}`);
            // 调用微信支付SDK
            WeixinJSBridge.invoke('getBrandWCPayRequest', response.data, (res) => {
                if (res.err_msg === "get_brand_wcpay_request:ok") {
                    this.checkPaymentStatus();
                }
            });
        } catch (error) {
            this.handleError(error);
        }
    }

    async handleAlipayPayment() {
        try {
            const response = await axios.post(`/payment/alipay/${this.queryId}`);
            // 跳转到支付宝支付页面
            window.location.href = response.data.pay_url;
        } catch (error) {
            this.handleError(error);
        }
    }

    async checkPaymentStatus() {
        // 轮询检查支付状态
        const checkStatus = async () => {
            const response = await axios.get(`/payment/status/${this.queryId}`);
            if (response.data.status === 'paid') {
                this.showReport(response.data.report);
            } else if (response.data.status === 'pending') {
                setTimeout(checkStatus, 2000);
            }
        };
        
        checkStatus();
    }

    showReport(report) {
        // 显示报告内容
        document.getElementById('reportContainer').innerHTML = report;
    }

    handleError(error) {
        alert(error.response?.data?.error || '支付过程中发生错误');
    }
}

// 初始化支付处理器
const paymentHandler = new PaymentHandler(); 