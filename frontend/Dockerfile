# Stage 1: Build Vue application with Node.js
FROM node:22-alpine AS builder

WORKDIR /app

# Copy dependency manifests first for layer caching
COPY package.json package-lock.json ./

# Clean install dependencies
RUN npm ci

# Copy project source code
COPY . .

# Build production bundle
RUN npm run build

# Stage 2: Serve built assets using Nginx (without node_modules)
FROM nginx:1.27-alpine

# Copy built static artifacts from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy custom Nginx configuration with reverse proxy support
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
