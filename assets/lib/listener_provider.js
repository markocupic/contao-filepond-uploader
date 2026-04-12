export class ListenerProvider {
    constructor() {
        this.listeners = new Map();
    }

    register(eventName, listener, priority = 0) {
        if (!this.listeners.has(eventName)) {
            this.listeners.set(eventName, []);
        }

        this.listeners.get(eventName).push({listener, priority});

        // Sortieren wie in Symfony
        this.listeners.get(eventName).sort((a, b) => b.priority - a.priority);
    }

    getListeners(eventName) {
        return (this.listeners.get(eventName) || []).map(l => l.listener);
    }
}
