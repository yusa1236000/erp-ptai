/**
 * WebSocketClient.js
 * A configurable WebSocket client that reads the WebSocket URL from environment variables.
 */

class WebSocketClient {
  constructor() {
    this.url = process.env.VUE_APP_WEBSOCKET_URL || 'ws://localhost:8080/ws';
    this.ws = null;
    this.reconnectInterval = 5000; // 5 seconds
    this.shouldReconnect = true;
    this.connect();
  }

  connect() {
    this.ws = new WebSocket(this.url);

    this.ws.onopen = () => {
      console.log('WebSocket connected to', this.url);
    };

    this.ws.onmessage = (event) => {
      console.log('WebSocket message received:', event.data);
      // Add your message handling logic here
    };

    this.ws.onclose = (event) => {
      console.log('WebSocket connection closed:', event.reason);
      if (this.shouldReconnect) {
        console.log(`Reconnecting in ${this.reconnectInterval / 1000} seconds...`);
        setTimeout(() => this.connect(), this.reconnectInterval);
      }
    };

    this.ws.onerror = (error) => {
      console.error('WebSocket error:', error);
      this.ws.close();
    };
  }

  send(data) {
    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      this.ws.send(data);
    } else {
      console.error('WebSocket is not open. Ready state:', this.ws.readyState);
    }
  }

  close() {
    this.shouldReconnect = false;
    if (this.ws) {
      this.ws.close();
    }
  }
}

const webSocketClient = new WebSocketClient();

export default webSocketClient;
