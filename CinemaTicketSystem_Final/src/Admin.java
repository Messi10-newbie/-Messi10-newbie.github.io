public class Admin extends Person {

    public Admin(String username, String password) {
        super(username, password);
    }

    @Override
    public String getRole() { 
        return "Admin"; 
    }

    @Override
    public String toString() {
        return "Admin[username=" + getUsername() + "]";
    }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Admin)) return false;
        return super.equals(obj);
    }
}