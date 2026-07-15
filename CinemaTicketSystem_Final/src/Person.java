import java.util.Objects;

public abstract class Person {
    private String username;
    private String password;

    public Person(String username, String password) {
        this.username = username;
        this.password = password;
    }

    public String getUsername() { return username; }
    public String getPassword() { return password; }
    public void setUsername(String username) { this.username = username; }
    public void setPassword(String password) { this.password = password; }

    public abstract String getRole();

    @Override
    public String toString() {
        return getRole() + "[username=" + username + "]";
    }

    @Override
    public int hashCode() { return Objects.hash(username); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Person)) return false;
        Person other = (Person) obj;
        return username.equals(other.username);
    }
}
