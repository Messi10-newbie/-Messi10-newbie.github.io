public class Customer extends Person {

    private int totalBookings;

    public Customer(String username, String password) {
        super(username, password);
        this.totalBookings = 0;
    }

    public int getTotalBookings() { return totalBookings; }
    public void incrementBookings() { this.totalBookings++; }

    @Override
    public String getRole() { return "Customer"; }

    @Override
    public String toString() {
        return "Customer[username=" + getUsername() + ", totalBookings=" + totalBookings + "]";
    }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Customer)) return false;
        return super.equals(obj);
    }
}
