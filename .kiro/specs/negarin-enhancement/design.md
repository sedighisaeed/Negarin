# Design Document

## Overview

This design document outlines the architecture and implementation approach for enhancing the existing Laravel-based Pixelfed platform to become the comprehensive Negarin platform as described in the requirements. The design maintains backward compatibility with existing functionality while adding new features through modular architecture patterns.

## Architecture

### High-Level Architecture

The enhanced Negarin platform will follow a modular monolithic architecture built on Laravel 12.x, with clear separation of concerns and the ability to extract modules into microservices in the future.

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend Layer                           │
├─────────────────────────────────────────────────────────────┤
│  Web UI (Blade/Vue.js) │ Mobile PWA │ Native Apps (Future)  │
├─────────────────────────────────────────────────────────────┤
│                    API Gateway Layer                        │
├─────────────────────────────────────────────────────────────┤
│  REST APIs │ GraphQL │ WebSocket │ ActivityPub Federation   │
├─────────────────────────────────────────────────────────────┤
│                  Application Layer                          │
├─────────────────────────────────────────────────────────────┤
│ Social Media │ NFT Market │ Workshops │ Auctions │ Wallet   │
├─────────────────────────────────────────────────────────────┤
│                   Service Layer                             │
├─────────────────────────────────────────────────────────────┤
│ User Service │ Media Service │ Blockchain │ Payment │ i18n   │
├─────────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                       │
├─────────────────────────────────────────────────────────────┤
│ Database │ Redis │ Queue │ Storage │ Search │ Monitoring     │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack Enhancements

**Backend Additions:**
- **FilamentPHP 3.x** - Modern admin panel framework
- **Web3.php** - Ethereum/Polygon blockchain integration
- **Pusher/Laravel WebSockets** - Real-time communication
- **Laravel Cashier** - Payment processing
- **Spatie Laravel Permission** - Enhanced role management
- **Laravel Scout + Elasticsearch** - Advanced search
- **Laravel Sanctum** - API authentication for mobile

**Frontend Enhancements:**
- **Vue.js 3** - Upgrade from Vue 2.6 for better performance
- **Inertia.js** - Modern SPA experience with Laravel
- **Web3.js/Ethers.js** - Blockchain wallet integration
- **Socket.io** - Real-time features
- **PWA Workbox** - Progressive web app capabilities

**Blockchain Integration:**
- **Polygon Network** - Primary blockchain for NFTs
- **IPFS** - Decentralized storage for NFT metadata
- **OpenZeppelin Contracts** - Secure smart contract templates
- **Hardhat** - Smart contract development and testing

## Components and Interfaces

### 1. FilamentPHP Admin Panel

**Core Admin Resources:**
```php
// Admin Resources Structure
app/Filament/Resources/
├── UserResource.php           // User management
├── ProfileResource.php        // Profile management  
├── StatusResource.php         // Content moderation
├── MediaResource.php          // Media management
├── NftResource.php           // NFT marketplace admin
├── WorkshopResource.php      // Workshop management
├── AuctionResource.php       // Auction administration
├── WalletResource.php        // Financial management
├── ReportResource.php        // Content reports
└── SettingsResource.php      // System configuration
```

**Admin Dashboard Widgets:**
- Real-time user statistics
- Platform revenue metrics
- Content moderation queue
- System health monitoring
- Blockchain transaction status

### 2. NFT Marketplace Module

**Database Schema:**
```sql
-- NFT Collections
CREATE TABLE nft_collections (
    id BIGINT PRIMARY KEY,
    profile_id BIGINT REFERENCES profiles(id),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    contract_address VARCHAR(42),
    blockchain VARCHAR(20) DEFAULT 'polygon',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- NFT Tokens
CREATE TABLE nft_tokens (
    id BIGINT PRIMARY KEY,
    collection_id BIGINT REFERENCES nft_collections(id),
    token_id VARCHAR(255),
    metadata_uri VARCHAR(500),
    owner_profile_id BIGINT REFERENCES profiles(id),
    creator_profile_id BIGINT REFERENCES profiles(id),
    price DECIMAL(20,8),
    currency VARCHAR(10),
    royalty_percentage DECIMAL(5,2),
    is_listed BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- NFT Transactions
CREATE TABLE nft_transactions (
    id BIGINT PRIMARY KEY,
    nft_token_id BIGINT REFERENCES nft_tokens(id),
    from_profile_id BIGINT REFERENCES profiles(id),
    to_profile_id BIGINT REFERENCES profiles(id),
    transaction_hash VARCHAR(66),
    transaction_type ENUM('mint', 'transfer', 'sale'),
    price DECIMAL(20,8),
    currency VARCHAR(10),
    gas_fee DECIMAL(20,8),
    status ENUM('pending', 'confirmed', 'failed'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**NFT Service Architecture:**
```php
interface NftServiceInterface
{
    public function mintNft(array $metadata, Profile $creator): NftToken;
    public function listForSale(NftToken $nft, float $price, string $currency): bool;
    public function purchaseNft(NftToken $nft, Profile $buyer): NftTransaction;
    public function transferOwnership(NftToken $nft, Profile $newOwner): bool;
    public function calculateRoyalties(NftToken $nft, float $salePrice): array;
}
```

### 3. Workshop System Module

**Database Schema:**
```sql
-- Workshops
CREATE TABLE workshops (
    id BIGINT PRIMARY KEY,
    instructor_profile_id BIGINT REFERENCES profiles(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    skill_level ENUM('beginner', 'intermediate', 'advanced'),
    price DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'USD',
    max_participants INTEGER,
    duration_minutes INTEGER,
    scheduled_at TIMESTAMP,
    status ENUM('draft', 'published', 'ongoing', 'completed', 'cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Workshop Enrollments
CREATE TABLE workshop_enrollments (
    id BIGINT PRIMARY KEY,
    workshop_id BIGINT REFERENCES workshops(id),
    participant_profile_id BIGINT REFERENCES profiles(id),
    payment_status ENUM('pending', 'paid', 'refunded'),
    completion_status ENUM('enrolled', 'in_progress', 'completed', 'dropped'),
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    certificate_issued_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Workshop Sessions
CREATE TABLE workshop_sessions (
    id BIGINT PRIMARY KEY,
    workshop_id BIGINT REFERENCES workshops(id),
    session_number INTEGER,
    title VARCHAR(255),
    description TEXT,
    video_url VARCHAR(500),
    duration_minutes INTEGER,
    resources JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4. Auction Platform Module

**Database Schema:**
```sql
-- Auctions
CREATE TABLE auctions (
    id BIGINT PRIMARY KEY,
    seller_profile_id BIGINT REFERENCES profiles(id),
    item_type ENUM('nft', 'artwork', 'physical'),
    item_id BIGINT, -- References nft_tokens.id or other item tables
    title VARCHAR(255) NOT NULL,
    description TEXT,
    starting_price DECIMAL(20,8),
    reserve_price DECIMAL(20,8),
    current_bid DECIMAL(20,8),
    currency VARCHAR(10),
    auction_type ENUM('english', 'dutch', 'sealed_bid'),
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    status ENUM('scheduled', 'active', 'ended', 'cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Auction Bids
CREATE TABLE auction_bids (
    id BIGINT PRIMARY KEY,
    auction_id BIGINT REFERENCES auctions(id),
    bidder_profile_id BIGINT REFERENCES profiles(id),
    bid_amount DECIMAL(20,8),
    is_proxy_bid BOOLEAN DEFAULT false,
    max_bid_amount DECIMAL(20,8),
    status ENUM('active', 'outbid', 'winning', 'withdrawn'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 5. Multi-Currency Wallet System

**Database Schema:**
```sql
-- Wallets
CREATE TABLE wallets (
    id BIGINT PRIMARY KEY,
    profile_id BIGINT REFERENCES profiles(id),
    currency VARCHAR(10),
    balance DECIMAL(20,8) DEFAULT 0,
    locked_balance DECIMAL(20,8) DEFAULT 0,
    wallet_address VARCHAR(255), -- For crypto wallets
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(profile_id, currency)
);

-- Wallet Transactions
CREATE TABLE wallet_transactions (
    id BIGINT PRIMARY KEY,
    wallet_id BIGINT REFERENCES wallets(id),
    transaction_type ENUM('deposit', 'withdrawal', 'transfer', 'payment', 'refund'),
    amount DECIMAL(20,8),
    fee DECIMAL(20,8) DEFAULT 0,
    reference_type VARCHAR(100), -- 'nft_purchase', 'workshop_payment', etc.
    reference_id BIGINT,
    external_transaction_id VARCHAR(255),
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled'),
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 6. Enhanced Communication System

**Database Schema:**
```sql
-- Conversations
CREATE TABLE conversations (
    id BIGINT PRIMARY KEY,
    type ENUM('direct', 'group'),
    title VARCHAR(255),
    created_by_profile_id BIGINT REFERENCES profiles(id),
    is_encrypted BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Conversation Participants
CREATE TABLE conversation_participants (
    id BIGINT PRIMARY KEY,
    conversation_id BIGINT REFERENCES conversations(id),
    profile_id BIGINT REFERENCES profiles(id),
    role ENUM('member', 'admin', 'moderator'),
    joined_at TIMESTAMP,
    left_at TIMESTAMP NULL,
    last_read_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Messages
CREATE TABLE messages (
    id BIGINT PRIMARY KEY,
    conversation_id BIGINT REFERENCES conversations(id),
    sender_profile_id BIGINT REFERENCES profiles(id),
    message_type ENUM('text', 'image', 'file', 'nft_share', 'workshop_share'),
    content TEXT,
    attachments JSON,
    reply_to_message_id BIGINT REFERENCES messages(id),
    is_encrypted BOOLEAN DEFAULT false,
    edited_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 7. Digital Certificate System

**Database Schema:**
```sql
-- Certificates
CREATE TABLE certificates (
    id BIGINT PRIMARY KEY,
    recipient_profile_id BIGINT REFERENCES profiles(id),
    issuer_profile_id BIGINT REFERENCES profiles(id),
    certificate_type ENUM('workshop_completion', 'skill_badge', 'achievement'),
    reference_type VARCHAR(100), -- 'workshop', 'course', 'skill_assessment'
    reference_id BIGINT,
    title VARCHAR(255),
    description TEXT,
    metadata JSON,
    blockchain_hash VARCHAR(66),
    ipfs_hash VARCHAR(100),
    verification_url VARCHAR(500),
    issued_at TIMESTAMP,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Certificate Templates
CREATE TABLE certificate_templates (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    template_data JSON, -- SVG/HTML template with placeholders
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Data Models

### Enhanced User and Profile Models

**User Model Extensions:**
```php
class User extends Authenticatable
{
    // Existing relationships...
    
    public function wallets()
    {
        return $this->hasManyThrough(Wallet::class, Profile::class);
    }
    
    public function nftTokens()
    {
        return $this->hasManyThrough(NftToken::class, Profile::class, 'user_id', 'owner_profile_id');
    }
    
    public function workshops()
    {
        return $this->hasManyThrough(Workshop::class, Profile::class, 'user_id', 'instructor_profile_id');
    }
    
    public function certificates()
    {
        return $this->hasManyThrough(Certificate::class, Profile::class, 'user_id', 'recipient_profile_id');
    }
}
```

**New Model Classes:**
```php
// NFT Models
class NftCollection extends Model
class NftToken extends Model  
class NftTransaction extends Model

// Workshop Models
class Workshop extends Model
class WorkshopEnrollment extends Model
class WorkshopSession extends Model

// Auction Models
class Auction extends Model
class AuctionBid extends Model

// Wallet Models
class Wallet extends Model
class WalletTransaction extends Model

// Communication Models
class Conversation extends Model
class ConversationParticipant extends Model
class Message extends Model

// Certificate Models
class Certificate extends Model
class CertificateTemplate extends Model
```

## Error Handling

### Centralized Error Management

**Custom Exception Classes:**
```php
// Blockchain Exceptions
class BlockchainException extends Exception
class InsufficientGasException extends BlockchainException
class TransactionFailedException extends BlockchainException

// Payment Exceptions  
class PaymentException extends Exception
class InsufficientFundsException extends PaymentException
class PaymentGatewayException extends PaymentException

// Workshop Exceptions
class WorkshopException extends Exception
class WorkshopFullException extends WorkshopException
class WorkshopNotStartedException extends WorkshopException

// Auction Exceptions
class AuctionException extends Exception
class AuctionEndedException extends AuctionException
class InvalidBidException extends AuctionException
```

**Global Error Handler:**
```php
class Handler extends ExceptionHandler
{
    protected $dontReport = [
        BlockchainException::class,
        PaymentException::class,
    ];

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof BlockchainException) {
            return response()->json([
                'error' => 'Blockchain operation failed',
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ], 422);
        }
        
        return parent::render($request, $exception);
    }
}
```

## Testing Strategy

### Comprehensive Testing Approach

**Unit Testing:**
- Service layer testing with mocked dependencies
- Model relationship and validation testing
- Utility class and helper function testing

**Integration Testing:**
- API endpoint testing with database transactions
- Blockchain integration testing with test networks
- Payment gateway integration testing with sandbox environments

**Feature Testing:**
- End-to-end user workflows (registration to NFT purchase)
- Workshop creation and completion flows
- Auction bidding and settlement processes

**Performance Testing:**
- Load testing for concurrent auction bidding
- Stress testing for real-time messaging
- Database query optimization validation

### Testing Tools and Configuration

```php
// phpunit.xml additions
<testsuites>
    <testsuite name="NFT">
        <directory suffix="Test.php">./tests/Feature/NFT</directory>
    </testsuite>
    <testsuite name="Workshop">
        <directory suffix="Test.php">./tests/Feature/Workshop</directory>
    </testsuite>
    <testsuite name="Auction">
        <directory suffix="Test.php">./tests/Feature/Auction</directory>
    </testsuite>
</testsuites>
```

## Security Considerations

### Enhanced Security Measures

**Blockchain Security:**
- Multi-signature wallet implementation for high-value transactions
- Smart contract audit requirements before deployment
- Gas fee optimization to prevent transaction failures
- Private key encryption and secure storage

**Financial Security:**
- PCI DSS compliance for payment processing
- KYC/AML verification for high-value transactions
- Transaction monitoring and fraud detection
- Secure escrow services for auctions and high-value sales

**Data Protection:**
- End-to-end encryption for sensitive communications
- GDPR compliance with data anonymization
- Regular security audits and penetration testing
- Comprehensive audit logging for all financial transactions

## Performance Optimization

### Caching Strategy

**Multi-Layer Caching:**
```php
// Redis caching for frequently accessed data
Cache::tags(['nft', 'marketplace'])->put("nft:{$id}", $nftData, 3600);

// Database query optimization
DB::enableQueryLog();
// Monitor and optimize N+1 queries

// CDN integration for media files
Storage::disk('s3')->url($mediaPath);
```

**Real-time Performance:**
- WebSocket connection pooling for auction bidding
- Redis pub/sub for real-time notifications
- Database read replicas for heavy read operations
- Elasticsearch for complex search queries

### Scalability Considerations

**Horizontal Scaling:**
- Load balancer configuration for multiple app instances
- Database sharding strategy for large datasets
- Microservice extraction plan for future growth
- CDN integration for global content delivery

## Deployment and Infrastructure

### Enhanced Infrastructure Requirements

**Additional Services:**
- **Elasticsearch** - Advanced search and analytics
- **Redis Cluster** - Distributed caching and sessions
- **WebSocket Server** - Real-time communication
- **IPFS Node** - Decentralized storage for NFT metadata
- **Blockchain Node** - Direct blockchain interaction (optional)

**Monitoring and Logging:**
- **Laravel Telescope** - Development debugging
- **Laravel Horizon** - Queue monitoring
- **Sentry** - Error tracking and performance monitoring
- **Prometheus + Grafana** - Infrastructure monitoring

**Security Infrastructure:**
- **WAF (Web Application Firewall)** - DDoS protection
- **SSL/TLS Certificates** - End-to-end encryption
- **VPN Access** - Secure admin access
- **Backup Strategy** - Automated database and file backups

This design provides a comprehensive foundation for implementing all the required features while maintaining the existing Laravel architecture and ensuring scalability, security, and maintainability.